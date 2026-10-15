<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DailyReportController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = ['user' => $request->integer('user') ?: null, 'date' => (string) $request->query('date', '')];

        return Inertia::render('Admin/Reports/Index', [
            'reports' => DailyReport::query()->with('user:id,name,position,avatar_path')
                ->when($filters['user'], fn ($w) => $w->where('user_id', $filters['user']))
                ->when($filters['date'] !== '', fn ($w) => $w->whereDate('date', $filters['date']))
                ->latest('date')->latest('id')->paginate(15)->withQueryString(),
            'users' => User::query()->where('role', User::ROLE_AGENT)->orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
            'todayCount' => DailyReport::query()->whereDate('date', today())->count(),
            'agentCount' => User::query()->where('role', User::ROLE_AGENT)->where('is_active', true)->count(),
        ]);
    }
}
