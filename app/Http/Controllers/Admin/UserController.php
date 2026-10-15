<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'role' => (string) $request->query('role', ''),
            'status' => (string) $request->query('status', ''),
        ];

        $users = User::query()
            ->when($filters['q'] !== '', fn ($w) => $w->where(fn ($x) => $x->where('name', 'like', '%'.$filters['q'].'%')->orWhere('email', 'like', '%'.$filters['q'].'%')->orWhere('employee_number', 'like', '%'.$filters['q'].'%')))
            ->when($filters['role'] !== '', fn ($w) => $w->where('role', $filters['role']))
            ->when($filters['status'] === 'active', fn ($w) => $w->where('is_active', true))
            ->when($filters['status'] === 'inactive', fn ($w) => $w->where('is_active', false))
            ->withCount(['assignedOrders', 'referredOrders'])
            ->orderBy('role')->orderBy('name')
            ->paginate(15)->withQueryString();

        $users->getCollection()->each(fn (User $u) => $u->setAttribute('two_factor_enabled', $u->hasTwoFactorEnabled()));

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $filters,
            'stats' => [
                'admins' => User::query()->where('role', User::ROLE_ADMIN)->count(),
                'agents' => User::query()->where('role', User::ROLE_AGENT)->count(),
                'inactive' => User::query()->where('is_active', false)->count(),
            ],
            'referralBase' => route('order.create'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, null);
        $plain = $data['password'] ?: Str::password(12, symbols: false);
        $user = User::query()->create(array_merge($data, ['password' => $plain, 'is_active' => true]));
        $user->forceFill(['email_verified_at' => now()])->save();
        AuditLog::record('user_created', 'Membuat akun '.$user->role.': '.$user->email, $user);

        return back()->with('success', 'Akun '.$user->name.' dibuat.')
            ->with('flash_data', ['credentials' => ['email' => $user->email, 'password' => $plain]]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);
        if ($user->id === $request->user()->id && $data['role'] !== User::ROLE_ADMIN) {
            return back()->with('error', 'Anda tidak dapat menurunkan peran akun Anda sendiri.');
        }
        $plain = $data['password'] ?? null;
        if (! $plain) {
            unset($data['password']);
        }
        $user->update($data);
        if ($plain) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }
        AuditLog::record('user_updated', 'Memperbarui akun '.$user->email.($plain ? ' (reset password)' : ''), $user);

        $res = back()->with('success', 'Akun diperbarui.');

        return $plain ? $res->with('flash_data', ['credentials' => ['email' => $user->email, 'password' => $plain]]) : $res;
    }

    public function toggle(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }
        $user->update(['is_active' => ! $user->is_active]);
        if (! $user->is_active) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }
        AuditLog::record($user->is_active ? 'user_activated' : 'user_deactivated', ($user->is_active ? 'Mengaktifkan' : 'Menonaktifkan').' akun '.$user->email, $user);

        return back()->with('success', $user->is_active ? 'Akun diaktifkan.' : 'Akun dinonaktifkan & dikeluarkan dari semua perangkat.');
    }

    public function resetTwoFactor(User $user): RedirectResponse
    {
        $user->forceFill(['two_factor_secret' => null, 'two_factor_confirmed_at' => null, 'two_factor_recovery_codes' => null])->save();
        AuditLog::record('user_2fa_reset', 'Reset 2FA akun '.$user->email, $user);

        return back()->with('success', '2FA akun '.$user->name.' direset.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }
        if ($user->isAdmin() && User::query()->where('role', User::ROLE_ADMIN)->count() <= 1) {
            return back()->with('error', 'Minimal harus ada satu admin.');
        }
        DB::table('sessions')->where('user_id', $user->id)->delete();
        AuditLog::record('user_deleted', 'Menghapus akun '.$user->email, $user);
        $user->delete();

        return back()->with('success', 'Akun dihapus.');
    }

    private function validated(Request $request, ?User $user): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_AGENT])],
            'position' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'employee_number' => ['nullable', 'string', 'max:50', Rule::unique('users', 'employee_number')->ignore($user?->id)],
            'joined_at' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:500'],
            'password' => ['nullable', 'string', Password::defaults()],
        ], [], ['name' => 'nama', 'role' => 'peran', 'employee_number' => 'nomor pegawai']) + ['password' => null];
    }
}
