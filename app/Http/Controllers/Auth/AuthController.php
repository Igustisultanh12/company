<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\TwoFactor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AuthController extends Controller
{
    private const DUMMY_HASH = '$2y$12$7oQ5mFQnQy5yWm8lZ9cJ4eS7k1V0c3y5b8n2m4p6r8t0v2x4z6A8C';

    public function create(): Response
    {
        return Inertia::render('Auth/Login', ['status' => session('status')]);
    }

    public function store(Request $request): RedirectResponse|SymfonyResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'remember' => ['boolean'],
        ]);

        $key = 'login:'.Str::lower($data['email']).'|'.$request->ip();
        $max = config('security.login_attempts', 5);

        if (RateLimiter::tooManyAttempts($key, $max)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        $user = User::query()->where('email', $data['email'])->first();
        $valid = Hash::check($data['password'], $user?->password ?? self::DUMMY_HASH);

        if (! $user || ! $valid) {
            RateLimiter::hit($key, 60);
            AuditLog::record('login_failed', 'Percobaan login gagal: '.$data['email'], $user, [], $user?->id);
            throw ValidationException::withMessages(['email' => 'Email atau password salah.']);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages(['email' => 'Akun Anda dinonaktifkan. Hubungi administrator.']);
        }

        RateLimiter::clear($key);

        if ($user->hasTwoFactorEnabled()) {
            $request->session()->put('login.2fa', [
                'id' => $user->id,
                'remember' => (bool) ($data['remember'] ?? false),
                'expires' => now()->addMinutes(5)->timestamp,
            ]);

            return redirect()->route('login.2fa');
        }

        return $this->completeLogin($request, $user, (bool) ($data['remember'] ?? false));
    }

    public function twoFactor(Request $request): Response|RedirectResponse
    {
        if (! $this->pendingUser($request)) {
            return redirect()->route('login');
        }

        return Inertia::render('Auth/TwoFactor');
    }

    public function twoFactorVerify(Request $request): RedirectResponse|SymfonyResponse
    {
        $user = $this->pendingUser($request);
        if (! $user) {
            return redirect()->route('login')->with('error', 'Sesi verifikasi berakhir, silakan login ulang.');
        }

        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:10'],
            'recovery_code' => ['nullable', 'string', 'max:20'],
        ]);

        $key = '2fa:'.$user->id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['code' => 'Terlalu banyak percobaan. Coba lagi dalam '.RateLimiter::availableIn($key).' detik.']);
        }

        $ok = ! empty($data['code'])
            ? TwoFactor::verify((string) $user->two_factor_secret, $data['code'])
            : (! empty($data['recovery_code']) && TwoFactor::useRecoveryCode($user, $data['recovery_code']));

        if (! $ok) {
            RateLimiter::hit($key, 300);
            AuditLog::record('login_2fa_failed', 'Kode 2FA salah', $user, [], $user->id);
            throw ValidationException::withMessages(['code' => 'Kode autentikasi tidak valid.']);
        }

        RateLimiter::clear($key);
        $remember = (bool) $request->session()->get('login.2fa.remember', false);
        $request->session()->forget('login.2fa');

        return $this->completeLogin($request, $user, $remember);
    }

    public function destroy(Request $request): SymfonyResponse
    {
        AuditLog::record('logout', 'Keluar dari sistem');
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->flash('success', 'Anda berhasil keluar.');

        return Inertia::location(route('login'));
    }

    private function pendingUser(Request $request): ?User
    {
        $pending = $request->session()->get('login.2fa');
        if (! $pending || ($pending['expires'] ?? 0) < now()->timestamp) {
            $request->session()->forget('login.2fa');

            return null;
        }

        return User::query()->where('is_active', true)->find($pending['id']);
    }

    private function completeLogin(Request $request, User $user, bool $remember): SymfonyResponse
    {
        Auth::login($user, $remember);
        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now(), 'last_login_ip' => $request->ip()])->saveQuietly();
        AuditLog::record('login', 'Login berhasil', $user, [], $user->id);

        $request->session()->flash('success', 'Selamat datang kembali, '.Str::before($user->name, ' ').'! 👋');
        $target = $request->session()->pull('url.intended', route($user->dashboardRoute()));

        // Muat ulang penuh agar daftar rute (Ziggy) untuk area internal ikut dimuat.
        return Inertia::location($this->safeTarget($target, $user));
    }

    private function safeTarget(string $url, User $user): string
    {
        $host = parse_url($url, PHP_URL_HOST);
        if ($host && $host !== request()->getHost()) {
            return route($user->dashboardRoute());
        }
        $path = '/'.ltrim((string) parse_url($url, PHP_URL_PATH), '/');
        if ($user->isAgent() && str_starts_with($path, '/admin')) {
            return route($user->dashboardRoute());
        }
        if ($user->isAdmin() && str_starts_with($path, '/agen')) {
            return route($user->dashboardRoute());
        }

        return $url;
    }
}
