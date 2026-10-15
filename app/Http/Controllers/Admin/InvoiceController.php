<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use App\Support\Catalog;
use App\Support\InvoiceView;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = ['q' => trim((string) $request->query('q', '')), 'status' => (string) $request->query('status', '')];

        $invoices = Invoice::query()
            ->with('order:id,code')
            ->when($filters['q'] !== '', fn ($w) => $w->where(fn ($x) => $x->where('number', 'like', '%'.$filters['q'].'%')->orWhere('client_name', 'like', '%'.$filters['q'].'%')))
            ->when($filters['status'] !== '', fn ($w) => $w->where('status', $filters['status']))
            ->latest('issue_date')->latest('id')
            ->paginate(15)->withQueryString();

        $active = Invoice::query()->where('status', '!=', 'cancelled')->where('status', '!=', 'draft');

        return Inertia::render('Admin/Invoices/Index', [
            'invoices' => $invoices,
            'filters' => $filters,
            'statuses' => Catalog::INVOICE_STATUSES,
            'stats' => [
                'billed' => (int) (clone $active)->sum('total'),
                'paid' => (int) (clone $active)->sum('paid_amount'),
                'overdue' => (clone $active)->whereIn('status', ['unpaid', 'partial'])->whereDate('due_date', '<', today())->count(),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $order = $request->integer('order') ? Order::query()->with('package:id,name,price')->find($request->integer('order')) : null;

        $draft = [
            'order_id' => $order?->id,
            'client_name' => $order ? ($order->company_name ? $order->company_name.' ('.$order->client_name.')' : $order->client_name) : '',
            'client_email' => $order?->client_email ?? '',
            'client_phone' => $order?->client_phone ?? '',
            'client_address' => '',
            'issue_date' => today()->toDateString(),
            'due_date' => today()->addDays(7)->toDateString(),
            'items' => [[
                'description' => $order ? 'Pembuatan '.$order->type_label.($order->package ? ' – Paket '.$order->package->name : '') : '',
                'qty' => 1,
                'price' => (int) ($order?->quoted_price ?: $order?->package?->price ?: 0),
            ]],
            'discount' => 0,
            'tax_percent' => (float) Setting::get('invoice_tax_percent', 0),
            'notes' => 'Pembayaran DP 50% sebelum pengerjaan dimulai, pelunasan setelah website selesai.',
            'status' => 'unpaid',
        ];

        return Inertia::render('Admin/Invoices/Form', ['invoice' => $draft, 'orders' => $this->orders(), 'statuses' => Catalog::INVOICE_STATUSES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $invoice = new Invoice($data);
        $invoice->number = Invoice::generateNumber();
        $invoice->created_by = $request->user()->id;
        $invoice->recalculate();
        $invoice->save();
        AuditLog::record('invoice_created', 'Membuat invoice '.$invoice->number, $invoice);

        if ($invoice->order && $invoice->status !== 'draft') {
            $invoice->order->log('Invoice '.$invoice->number.' telah diterbitkan.', 'system', true);
        }

        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Invoice '.$invoice->number.' dibuat.');
    }

    public function show(Invoice $invoice): Response
    {
        $invoice->load(['order:id,code,client_name', 'payments.recorder:id,name', 'creator:id,name']);

        return Inertia::render('Admin/Invoices/Show', [
            'invoice' => $invoice,
            'statuses' => Catalog::INVOICE_STATUSES,
            'publicUrl' => $invoice->status !== 'draft' ? URL::temporarySignedRoute('invoice.public', now()->addDays(14), ['invoice' => $invoice->id]) : null,
            'bank' => [
                'name' => Setting::get('invoice_bank_name'),
                'account' => Setting::get('invoice_bank_account'),
                'holder' => Setting::get('invoice_bank_holder'),
            ],
        ]);
    }

    public function edit(Invoice $invoice): Response
    {
        return Inertia::render('Admin/Invoices/Form', [
            'invoice' => array_merge($invoice->toArray(), [
                'issue_date' => $invoice->issue_date?->toDateString(),
                'due_date' => $invoice->due_date?->toDateString(),
            ]),
            'orders' => $this->orders(),
            'statuses' => Catalog::INVOICE_STATUSES,
        ]);
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $invoice->fill($this->validated($request));
        $invoice->recalculate();
        $invoice->save();
        $invoice->syncPaymentStatus();
        AuditLog::record('invoice_updated', 'Memperbarui invoice '.$invoice->number, $invoice);

        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Invoice diperbarui.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        AuditLog::record('invoice_deleted', 'Menghapus invoice '.$invoice->number, $invoice);
        $invoice->delete();

        return redirect()->route('admin.invoices.index')->with('success', 'Invoice dihapus.');
    }

    public function print(Invoice $invoice): View
    {
        return InvoiceView::make($invoice, true);
    }

    public function addPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        abort_if(in_array($invoice->status, ['draft', 'cancelled'], true), 422, 'Invoice draf/dibatalkan tidak dapat menerima pembayaran.');

        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1', 'max:'.max(1, $invoice->balance)],
            'paid_at' => ['required', 'date', 'before_or_equal:today'],
            'method' => ['required', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:255'],
        ], ['amount.max' => 'Jumlah melebihi sisa tagihan.'], ['amount' => 'jumlah', 'paid_at' => 'tanggal bayar', 'method' => 'metode']);

        DB::transaction(function () use ($invoice, $data, $request) {
            $invoice->payments()->create($data + ['recorded_by' => $request->user()->id]);
            $invoice->syncPaymentStatus();
        });

        AuditLog::record('payment_recorded', 'Mencatat pembayaran Rp'.number_format($data['amount'], 0, ',', '.').' untuk '.$invoice->number, $invoice);
        if ($invoice->order) {
            $invoice->order->log('Pembayaran untuk invoice '.$invoice->number.' telah kami terima. Terima kasih! 🙏', 'system', true);
        }

        return back()->with('success', 'Pembayaran dicatat.');
    }

    public function deletePayment(Payment $payment): RedirectResponse
    {
        $invoice = $payment->invoice;
        $payment->delete();
        $invoice?->syncPaymentStatus();
        AuditLog::record('payment_deleted', 'Menghapus pembayaran pada '.$invoice?->number, $invoice);

        return back()->with('success', 'Pembayaran dihapus.');
    }

    private function orders()
    {
        return Order::query()->latest()->limit(200)->get(['id', 'code', 'client_name', 'company_name']);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'client_name' => ['required', 'string', 'max:150'],
            'client_email' => ['nullable', 'email', 'max:150'],
            'client_phone' => ['nullable', 'string', 'max:30'],
            'client_address' => ['nullable', 'string', 'max:500'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01', 'max:100000'],
            'items.*.price' => ['required', 'integer', 'min:0', 'max:100000000000'],
            'discount' => ['nullable', 'integer', 'min:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['draft', 'unpaid', 'cancelled', 'partial', 'paid'])],
        ], [], ['client_name' => 'nama klien', 'items' => 'item', 'issue_date' => 'tanggal terbit', 'due_date' => 'jatuh tempo']);

        $data['discount'] = (int) ($data['discount'] ?? 0);
        $data['tax_percent'] = (float) ($data['tax_percent'] ?? 0);
        $data['items'] = array_values(array_map(fn ($i) => [
            'description' => (string) $i['description'],
            'qty' => (float) $i['qty'],
            'price' => (int) $i['price'],
        ], $data['items']));

        return $data;
    }
}
