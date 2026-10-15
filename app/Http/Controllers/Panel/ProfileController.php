<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Support\Uploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user();

        $sessions = [];
        if (config('session.driver') === 'database') {
            $sessions = DB::table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)
                ->orderByDesc('last_activity')
                ->limit(10)
                ->get()
                ->map(fn ($s) => [
                    'id' => $s->id,
                    'ip' => $s->ip_address,
                    'agent' => self::describeAgent((string) $s->user_agent),
                    'last_active' => date(DATE_ATOM, (int) $s->last_activity),
                    'current' => $s->id === $request->session()->getId(),
                ]);
        }

        return Inertia::render('Panel/Profile/Edit', [
            'profile' => $user->only(['name', 'email', 'phone', 'position', 'address', 'employee_number', 'referral_code', 'role', 'joined_at', 'avatar_url', 'initials', 'last_login_at', 'last_login_ip']),
            'twoFactor' => [
                'enabled' => $user->hasTwoFactorEnabled(),
                'pending' => $request->session()->has('2fa_setup_secret'),
                'qr' => $request->session()->get('2fa_setup_qr'),
                'secret' => $request->session()->get('2fa_setup_secret'),
                'recovery_left' => $user->hasTwoFactorEnabled() ? count((array) $user->two_factor_recovery_codes) : 0,
            ],
            'sessions' => $sessions,
            'tab' => (string) $request->query('tab', 'profile'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);
        $user->update($data);
        AuditLog::record('profile_updated', 'Memperbarui profil');

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function password(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults(), 'different:current_password'],
        ], [], ['current_password' => 'password saat ini', 'password' => 'password baru']);

        $request->user()->update(['password' => $request->password]);
        AuditLog::record('password_changed', 'Mengganti password');

        // Keluarkan sesi di perangkat lain demi keamanan.
        Auth::logoutOtherDevices($request->password);
        $this->purgeOtherSessions($request);

        return back()->with('success', 'Password berhasil diganti. Sesi di perangkat lain telah dikeluarkan.');
    }

    public function avatar(Request $request): RedirectResponse
    {
        $request->validate(['avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072']]);
        $user = $request->user();
        $path = Uploads::publicImage($request->file('avatar'), 'avatars', 480);
        Uploads::deletePublic($user->avatar_path);
        $user->update(['avatar_path' => $path]);

        return back()->with('success', 'Foto profil diperbarui.');
    }

    public function destroySession(Request $request, string $session): RedirectResponse
    {
        if ($session === $request->session()->getId()) {
            return back()->with('error', 'Tidak bisa menghapus sesi yang sedang dipakai. Gunakan tombol Keluar.');
        }
        DB::table(config('session.table', 'sessions'))->where('id', $session)->where('user_id', $request->user()->id)->delete();
        AuditLog::record('session_revoked', 'Mengakhiri satu sesi perangkat');

        return back()->with('success', 'Sesi perangkat telah diakhiri.');
    }

    public function destroyOtherSessions(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);
        Auth::logoutOtherDevices($request->password);
        $this->purgeOtherSessions($request);
        AuditLog::record('session_revoked', 'Mengakhiri semua sesi perangkat lain');

        return back()->with('success', 'Semua sesi di perangkat lain telah diakhiri.');
    }

    private function purgeOtherSessions(Request $request): void
    {
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $request->user()->id)
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }
    }

    public static function describeAgent(string $ua): string
    {
        $browser = match (true) {
            str_contains($ua, 'Edg/') => 'Edge',
            str_contains($ua, 'OPR/') => 'Opera',
            str_contains($ua, 'Chrome/') => 'Chrome',
            str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Safari/') => 'Safari',
            default => 'Browser',
        };
        $os = match (true) {
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') => 'iOS',
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Mac OS') => 'macOS',
            str_contains($ua, 'Linux') => 'Linux',
            default => 'Perangkat',
        };

        return "{$browser} di {$os}";
    }
}
