<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\ContactMessage;
use App\Models\Invoice;
use App\Models\LeaveRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SignatureRequest;
use App\Models\Task;
use App\Models\User;
use App\Support\Catalog;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $months = collect(range(5, 0))->map(fn ($i) => now()->startOfMonth()->subMonths($i));
        $from = $months->first()->copy();

        $orders = Order::query()->where('created_at', '>=', $from)->get(['id', 'created_at']);
        $payments = Payment::query()->where('paid_at', '>=', $from)->get(['amount', 'paid_at']);

        $chart = $months->map(fn (Carbon $m) => [
            'label' => $m->translatedFormat('M Y'),
            'orders' => $orders->filter(fn ($o) => $o->created_at->isSameMonth($m))->count(),
            'revenue' => (int) $payments->filter(fn ($p) => $p->paid_at->isSameMonth($m))->sum('amount'),
        ]);

        $statusCounts = Order::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $activeAgents = User::query()->where('role', User::ROLE_AGENT)->where('is_active', true)->count();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'orders_new' => (int) ($statusCounts['new'] ?? 0),
                'orders_active' => (int) collect(['reviewing', 'quoted', 'deal', 'in_progress', 'revision'])->sum(fn ($s) => $statusCounts[$s] ?? 0),
                'orders_completed' => (int) ($statusCounts['completed'] ?? 0),
                'orders_total' => (int) $statusCounts->sum(),
                'revenue_month' => (int) Payment::query()->where('paid_at', '>=', now()->startOfMonth())->sum('amount'),
                'revenue_year' => (int) Payment::query()->where('paid_at', '>=', now()->startOfYear())->sum('amount'),
                'receivable' => (int) Invoice::query()->whereIn('status', ['unpaid', 'partial'])->get(['total', 'paid_amount'])->sum(fn ($i) => max(0, $i->total - $i->paid_amount)),
                'tte_pending' => SignatureRequest::query()->where('status', 'pending')->count(),
                'messages_unread' => ContactMessage::query()->where('is_read', false)->count(),
                'leaves_pending' => LeaveRequest::query()->where('status', 'pending')->count(),
                'agents_active' => $activeAgents,
                'present_today' => Attendance::query()->whereDate('date', today())->whereNotNull('check_in_at')->count(),
            ],
            'chart' => $chart,
            'statusChart' => collect(Catalog::ORDER_STATUSES)->map(fn ($v, $k) => ['key' => $k, 'label' => $v['label'], 'color' => $v['color'], 'total' => (int) ($statusCounts[$k] ?? 0)])->values(),
            'recentOrders' => Order::query()->with('assignee:id,name,avatar_path')->latest()->limit(6)->get(['id', 'code', 'client_name', 'company_name', 'website_type', 'status', 'progress', 'assigned_to', 'created_at']),
            'pendingTte' => SignatureRequest::query()->where('status', 'pending')->with('user:id,name,avatar_path')->latest()->limit(5)->get(['id', 'code', 'title', 'user_id', 'created_at', 'status']),
            'upcomingTasks' => Task::query()->where('status', '!=', 'done')->whereNotNull('due_date')->orderBy('due_date')->with('assignee:id,name,avatar_path')->limit(6)->get(['id', 'title', 'status', 'priority', 'due_date', 'assigned_to']),
            'activities' => AuditLog::query()->with('user:id,name,avatar_path')->latest('created_at')->limit(8)->get(),
            'compliance' => ComplianceController::summary(),
        ]);
    }
}
