<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'user' => $request->integer('user') ?: null,
            'action' => (string) $request->query('action', ''),
            'security' => $request->boolean('security'),
        ];

        $securityActions = ['login', 'login_failed', 'login_2fa_failed', 'logout', 'password_changed', 'password_reset', '2fa_enabled', '2fa_disabled', 'session_revoked', 'tte_pin_failed', 'user_2fa_reset'];

        return Inertia::render('Admin/Audit/Index', [
            'logs' => AuditLog::query()->with('user:id,name,avatar_path,role')
                ->when($filters['q'] !== '', fn ($w) => $w->where(fn ($x) => $x->where('description', 'like', '%'.$filters['q'].'%')->orWhere('ip_address', 'like', '%'.$filters['q'].'%')))
                ->when($filters['user'], fn ($w) => $w->where('user_id', $filters['user']))
                ->when($filters['action'] !== '', fn ($w) => $w->where('action', $filters['action']))
                ->when($filters['security'], fn ($w) => $w->whereIn('action', $securityActions))
                ->latest('created_at')->latest('id')->paginate(25)->withQueryString(),
            'users' => User::withTrashed()->orderBy('name')->get(['id', 'name']),
            'actions' => AuditLog::query()->distinct()->orderBy('action')->pluck('action'),
            'filters' => $filters,
            'failedToday' => AuditLog::query()->whereIn('action', ['login_failed', 'login_2fa_failed', 'tte_pin_failed'])->where('created_at', '>=', today())->count(),
        ]);
    }
}
