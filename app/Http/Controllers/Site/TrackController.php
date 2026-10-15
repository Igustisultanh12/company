<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use App\Support\Catalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TrackController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Site/Track/Index', [
            'meta' => ['title' => 'Lacak Pesanan – '.Setting::get('company_name'), 'description' => 'Pantau status & progres pembuatan website kamu.'],
            'code' => (string) $request->query('kode', ''),
        ]);
    }

    public function lookup(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:150'],
        ], [], ['code' => 'kode pesanan']);

        $order = Order::query()
            ->where('code', strtoupper(trim($data['code'])))
            ->where('client_email', Str::lower(trim($data['email'])))
            ->first();

        if (! $order) {
            return back()->withErrors(['code' => 'Pesanan tidak ditemukan. Periksa kembali kode & email yang kamu gunakan.']);
        }

        $tracked = (array) $request->session()->get('tracked_orders', []);
        $tracked[] = $order->id;
        $request->session()->put('tracked_orders', array_values(array_unique($tracked)));

        return redirect()->route('track.show', $order->code);
    }

    public function show(Request $request, string $code): Response|RedirectResponse
    {
        $order = Order::query()->where('code', $code)->with(['package:id,name', 'activities' => fn ($q) => $q->where('is_public', true)])->first();

        if (! $order || ! in_array($order->id, (array) $request->session()->get('tracked_orders', []), true)) {
            return redirect()->route('track', ['kode' => $code])->with('warning', 'Masukkan email yang terdaftar untuk melihat detail pesanan.');
        }

        $invoices = $order->invoices()->whereNotIn('status', ['draft'])->get()->map(fn ($inv) => [
            'number' => $inv->number,
            'total' => $inv->total,
            'balance' => $inv->balance,
            'status' => $inv->status,
            'status_label' => $inv->status_label,
            'due_date' => $inv->due_date?->toDateString(),
            'url' => URL::temporarySignedRoute('invoice.public', now()->addDays(7), ['invoice' => $inv->id]),
        ]);

        return Inertia::render('Site/Track/Show', [
            'meta' => ['title' => 'Status Pesanan '.$order->code, 'description' => ''],
            'order' => [
                'code' => $order->code,
                'client_name' => $order->client_name,
                'type_label' => $order->type_label,
                'package' => $order->package?->name,
                'status' => $order->status,
                'status_label' => $order->status_label,
                'progress' => $order->progress,
                'deadline' => $order->deadline?->toDateString(),
                'created_at' => $order->created_at->toIso8601String(),
                'completed_at' => $order->completed_at?->toIso8601String(),
                'activities' => $order->activities->map(fn ($a) => [
                    'id' => $a->id, 'type' => $a->type, 'message' => $a->message, 'created_at' => $a->created_at->toIso8601String(),
                ]),
            ],
            'invoices' => $invoices,
            'statuses' => Catalog::ORDER_STATUSES,
        ]);
    }
}
