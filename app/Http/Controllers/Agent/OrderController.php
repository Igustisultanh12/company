<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\UserNotification;
use App\Support\Catalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $tab = $request->query('tab') === 'referral' ? 'referral' : 'assigned';

        $orders = Order::query()
            ->when($tab === 'assigned', fn ($w) => $w->where('assigned_to', $user->id))
            ->when($tab === 'referral', fn ($w) => $w->where('referral_agent_id', $user->id))
            ->with('package:id,name')
            ->latest()->paginate(12)->withQueryString();

        if ($tab === 'referral') {
            // Data kontak klien referral tidak ditampilkan kecuali agen juga PIC.
            $orders->getCollection()->each(function (Order $o) use ($user) {
                if ($o->assigned_to !== $user->id) {
                    $o->makeHidden(['client_email', 'client_phone', 'internal_notes', 'ip_address', 'quoted_price']);
                }
            });
        }

        return Inertia::render('Agent/Orders/Index', [
            'orders' => $orders,
            'tab' => $tab,
            'counts' => [
                'assigned' => Order::query()->where('assigned_to', $user->id)->count(),
                'referral' => Order::query()->where('referral_agent_id', $user->id)->count(),
            ],
            'statuses' => Catalog::ORDER_STATUSES,
            'referralUrl' => $user->referral_code ? route('order.create', ['ref' => $user->referral_code]) : null,
        ]);
    }

    public function show(Request $request, Order $order): Response
    {
        $user = $request->user();
        abort_unless($order->assigned_to === $user->id || $order->referral_agent_id === $user->id, 403);
        $isPic = $order->assigned_to === $user->id;

        $order->load(['package:id,name', 'activities.user:id,name,avatar_path', 'tasks' => fn ($q) => $q->where('assigned_to', $user->id)]);
        $order->makeHidden(['ip_address', 'internal_notes']);
        if (! $isPic) {
            $order->makeHidden(['client_email', 'client_phone', 'quoted_price']);
            $order->setRelation('activities', $order->activities->where('is_public', true)->values());
        }

        return Inertia::render('Agent/Orders/Show', [
            'order' => $order,
            'isPic' => $isPic,
            'catalog' => Catalog::forFrontend(),
        ]);
    }

    public function progress(Request $request, Order $order): RedirectResponse
    {
        $user = $request->user();
        abort_unless($order->assigned_to === $user->id, 403);
        $data = $request->validate([
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
            'message' => ['required', 'string', 'max:2000'],
            'is_public' => ['boolean'],
            'request_review' => ['boolean'],
        ], [], ['message' => 'catatan progres']);

        $order->progress = $data['progress'];
        if ($order->status === 'deal' || $order->status === 'reviewing') {
            $order->status = 'in_progress';
            $order->started_at ??= now();
        }
        $order->save();
        $order->log($data['message'].' (progres '.$data['progress'].'%)', 'progress', (bool) ($data['is_public'] ?? false));

        $admins = User::query()->where('role', User::ROLE_ADMIN)->where('is_active', true)->pluck('id')->all();
        UserNotification::send(
            $admins,
            ($data['request_review'] ?? false ? '🔍 Minta review: ' : 'Update progres: ').$order->code.' ('.$data['progress'].'%)',
            $user->name.': '.$data['message'],
            route('admin.orders.show', $order),
            ($data['request_review'] ?? false) ? 'warning' : 'info'
        );

        return back()->with('success', 'Progres diperbarui.');
    }
}
