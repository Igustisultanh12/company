<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\DailyReport;
use App\Models\LeaveRequest;
use App\Models\Order;
use App\Models\Setting;
use App\Models\SignatureRequest;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $monthStart = now()->startOfMonth()->toDateString();

        $tasks = Task::query()->where('assigned_to', $user->id);

        return Inertia::render('Agent/Dashboard', [
            'today' => Attendance::query()->where('user_id', $user->id)->where('date', today()->toDateString())->first(),
            'workHours' => ['start' => Setting::get('attendance_start', '08:00'), 'end' => Setting::get('attendance_end', '16:00')],
            'stats' => [
                'tasks_open' => (clone $tasks)->where('status', '!=', 'done')->count(),
                'tasks_overdue' => (clone $tasks)->where('status', '!=', 'done')->whereDate('due_date', '<', today())->count(),
                'tasks_done_month' => (clone $tasks)->where('status', 'done')->where('completed_at', '>=', now()->startOfMonth())->count(),
                'present_month' => Attendance::query()->where('user_id', $user->id)->where('date', '>=', $monthStart)->whereIn('status', ['present', 'late'])->count(),
                'late_month' => Attendance::query()->where('user_id', $user->id)->where('date', '>=', $monthStart)->where('status', 'late')->count(),
                'orders_active' => Order::query()->where('assigned_to', $user->id)->whereNotIn('status', ['completed', 'cancelled'])->count(),
                'referrals' => Order::query()->where('referral_agent_id', $user->id)->count(),
                'referrals_completed' => Order::query()->where('referral_agent_id', $user->id)->where('status', 'completed')->count(),
                'tte_pending' => SignatureRequest::query()->where('user_id', $user->id)->where('status', 'pending')->count(),
                'leave_pending' => LeaveRequest::query()->where('user_id', $user->id)->where('status', 'pending')->count(),
            ],
            'reportToday' => DailyReport::query()->where('user_id', $user->id)->where('date', today()->toDateString())->exists(),
            'tasks' => (clone $tasks)->where('status', '!=', 'done')->with('order:id,code')
                ->orderByRaw('case when due_date is null then 1 else 0 end')->orderBy('due_date')->limit(6)->get(),
            'orders' => Order::query()->where('assigned_to', $user->id)->whereNotIn('status', ['completed', 'cancelled'])->latest()->limit(5)->get(['id', 'code', 'client_name', 'website_type', 'status', 'progress', 'deadline']),
            'announcements' => Announcement::query()->current()->with('creator:id,name')->limit(4)->get(),
            'referral' => [
                'code' => $user->referral_code,
                'url' => $user->referral_code ? route('order.create', ['ref' => $user->referral_code]) : null,
            ],
        ]);
    }
}
