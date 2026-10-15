<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use App\Models\UserNotification;
use App\Support\Catalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $this->filters($request);

        $orders = $this->query($filters)
            ->with(['assignee:id,name,avatar_path', 'package:id,name', 'referralAgent:id,name'])
            ->paginate(15)->withQueryString();

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'filters' => $filters,
            'counts' => Order::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'agents' => $this->staff(),
            'packages' => Package::query()->orderBy('sort_order')->get(['id', 'name']),
            'catalog' => Catalog::forFrontend(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules(true));
        $order = Order::query()->create(array_merge($data, [
            'code' => Order::generateCode(),
            'client_email' => Str::lower($data['client_email']),
            'status' => $data['status'] ?? 'new',
            'source' => 'manual',
            'features' => $data['features'] ?? [],
            'progress' => $data['progress'] ?? 0,
            'has_domain' => (bool) ($data['has_domain'] ?? false),
            'has_hosting' => (bool) ($data['has_hosting'] ?? false),
        ]));
        $order->log('Pesanan dibuat manual oleh admin.', 'system', true);
        AuditLog::record('order_created', 'Membuat pesanan manual '.$order->code, $order);
        $this->notifyAssignee($order, null);

        return redirect()->route('admin.orders.show', $order)->with('success', 'Pesanan dibuat dengan kode '.$order->code);
    }

    public function show(Order $order): Response
    {
        $order->load([
            'assignee:id,name,avatar_path,position', 'referralAgent:id,name,referral_code', 'package:id,name,price',
            'activities.user:id,name,avatar_path', 'invoices', 'tasks.assignee:id,name,avatar_path',
        ]);

        return Inertia::render('Admin/Orders/Show', [
            'order' => $order,
            'agents' => $this->staff(),
            'packages' => Package::query()->orderBy('sort_order')->get(['id', 'name', 'price']),
            'catalog' => Catalog::forFrontend(),
            'trackUrl' => route('track', ['kode' => $order->code]),
        ]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate($this->rules(false));
        $oldStatus = $order->status;
        $oldAssignee = $order->assigned_to;
        $oldProgress = $order->progress;

        $data['client_email'] = Str::lower($data['client_email']);
        $data['features'] = $data['features'] ?? [];
        $order->fill($data);
        if ($order->status === 'in_progress' && ! $order->started_at) {
            $order->started_at = now();
        }
        if ($order->status === 'completed') {
            $order->completed_at ??= now();
            $order->progress = 100;
        } elseif ($oldStatus === 'completed') {
            $order->completed_at = null;
        }
        $order->save();

        if ($oldStatus !== $order->status) {
            $note = trim((string) $request->input('status_note', ''));
            $order->log('Status diperbarui menjadi "'.$order->status_label.'".'.($note !== '' ? ' '.$note : ''), 'status', true, null, ['from' => $oldStatus, 'to' => $order->status]);
        }
        if ($oldProgress !== $order->progress && $oldStatus === $order->status) {
            $order->log('Progres pengerjaan '.$order->progress.'%.', 'progress', true);
        }
        $this->notifyAssignee($order, $oldAssignee);
        AuditLog::record('order_updated', 'Memperbarui pesanan '.$order->code, $order, ['status' => $order->status]);

        return back()->with('success', 'Pesanan diperbarui.');
    }

    public function activity(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'is_public' => ['boolean'],
        ], [], ['message' => 'catatan']);
        $order->log($data['message'], 'comment', (bool) ($data['is_public'] ?? false));

        return back()->with('success', ($data['is_public'] ?? false) ? 'Update dikirim & terlihat oleh klien di halaman lacak.' : 'Catatan internal ditambahkan.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        AuditLog::record('order_deleted', 'Menghapus pesanan '.$order->code, $order);
        $order->delete();

        return redirect()->route('admin.orders.index')->with('success', 'Pesanan dipindahkan ke tempat sampah.');
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);
        $rows = $this->query($filters)->with(['assignee:id,name', 'package:id,name'])->get();
        AuditLog::record('order_exported', 'Ekspor data pesanan ('.$rows->count().' baris)');

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Kode', 'Tanggal', 'Klien', 'Perusahaan', 'Email', 'WhatsApp', 'Jenis', 'Paket', 'Budget', 'Status', 'Progres', 'Penawaran', 'PIC', 'Sumber']);
            foreach ($rows as $o) {
                fputcsv($out, array_map([self::class, 'csvSafe'], [
                    $o->code, $o->created_at->format('Y-m-d H:i'), $o->client_name, $o->company_name, $o->client_email, $o->client_phone,
                    $o->type_label, $o->package?->name, $o->budget_range, $o->status_label, $o->progress.'%', $o->quoted_price, $o->assignee?->name, $o->source,
                ]));
            }
            fclose($out);
        }, 'pesanan-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Cegah CSV/Formula injection saat dibuka di Excel. */
    public static function csvSafe(mixed $v): string
    {
        $v = (string) $v;

        return preg_match('/^[=+\-@\t\r]/', $v) ? "'".$v : $v;
    }

    private function filters(Request $request): array
    {
        return [
            'q' => trim((string) $request->query('q', '')),
            'status' => (string) $request->query('status', ''),
            'assignee' => $request->integer('assignee') ?: null,
            'type' => (string) $request->query('type', ''),
            'source' => (string) $request->query('source', ''),
        ];
    }

    private function query(array $f)
    {
        return Order::query()
            ->when($f['q'] !== '', fn ($w) => $w->where(fn ($x) => $x
                ->where('code', 'like', '%'.$f['q'].'%')
                ->orWhere('client_name', 'like', '%'.$f['q'].'%')
                ->orWhere('company_name', 'like', '%'.$f['q'].'%')
                ->orWhere('client_email', 'like', '%'.$f['q'].'%')
                ->orWhere('client_phone', 'like', '%'.$f['q'].'%')))
            ->when($f['status'] !== '', fn ($w) => $w->where('status', $f['status']))
            ->when($f['assignee'], fn ($w) => $w->where('assigned_to', $f['assignee']))
            ->when($f['type'] !== '', fn ($w) => $w->where('website_type', $f['type']))
            ->when($f['source'] !== '', fn ($w) => $w->where('source', $f['source']))
            ->latest();
    }

    private function staff()
    {
        return User::query()->where('is_active', true)->orderBy('role')->orderBy('name')->get(['id', 'name', 'role', 'position', 'avatar_path']);
    }

    private function notifyAssignee(Order $order, ?int $old): void
    {
        if ($order->assigned_to && $order->assigned_to !== $old && $order->assigned_to !== auth()->id()) {
            UserNotification::send($order->assigned_to, 'Kamu ditugaskan ke pesanan '.$order->code, $order->client_name.' • '.$order->type_label, route('agent.orders.show', $order), 'info');
        }
    }

    private function rules(bool $creating): array
    {
        return [
            'client_name' => ['required', 'string', 'max:100'],
            'client_email' => ['required', 'email', 'max:150'],
            'client_phone' => ['required', 'string', 'max:30'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'website_type' => ['required', Rule::in(array_keys(Catalog::WEBSITE_TYPES))],
            'package_id' => ['nullable', 'integer', 'exists:packages,id'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', Rule::in(array_keys(Catalog::WEBSITE_FEATURES))],
            'budget_range' => ['nullable', Rule::in(array_keys(Catalog::BUDGET_RANGES))],
            'pages_estimate' => ['nullable', Rule::in(array_keys(Catalog::PAGES_ESTIMATES))],
            'deadline' => ['nullable', 'date'],
            'reference_urls' => ['nullable', 'string', 'max:1000'],
            'description' => ['required', 'string', 'max:5000'],
            'color_preference' => ['nullable', 'string', 'max:100'],
            'has_domain' => ['boolean'],
            'has_hosting' => ['boolean'],
            'status' => [$creating ? 'nullable' : 'required', Rule::in(array_keys(Catalog::ORDER_STATUSES))],
            'priority' => ['required', Rule::in(array_keys(Catalog::ORDER_PRIORITIES))],
            'quoted_price' => ['nullable', 'integer', 'min:0', 'max:100000000000'],
            'progress' => [$creating ? 'nullable' : 'required', 'integer', 'min:0', 'max:100'],
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')->where('is_active', true)],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
