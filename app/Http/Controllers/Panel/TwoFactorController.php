<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Support\TwoFactor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    public function enable(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);
        $secret = TwoFactor::generateSecret();
        $request->session()->put('2fa_setup_secret', $secret);
        $request->session()->put('2fa_setup_qr', TwoFactor::qrSvg(TwoFactor::otpauthUrl($request->user(), $secret)));

        return redirect()->route('panel.profile.edit', ['tab' => 'security']);
    }

    public function confirm(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:10']]);
        $secret = $request->session()->get('2fa_setup_secret');
        if (! $secret) {
            return back()->with('error', 'Mulai ulang proses aktivasi 2FA.');
        }

        $key = '2fa-setup:'.$request->user()->id;
        if (RateLimiter::tooManyAttempts($key, 6)) {
            throw ValidationException::withMessages(['code' => 'Terlalu banyak percobaan. Tunggu sebentar.']);
        }
        if (! TwoFactor::verify($secret, $request->code)) {
            RateLimiter::hit($key, 120);
            throw ValidationException::withMessages(['code' => 'Kode tidak cocok. Pastikan jam di HP sudah sinkron.']);
        }

        $codes = TwoFactor::generateRecoveryCodes();
        $request->user()->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $codes,
        ])->save();
        $request->session()->forget(['2fa_setup_secret', '2fa_setup_qr']);
        AuditLog::record('2fa_enabled', 'Mengaktifkan autentikasi dua faktor');

        return redirect()->route('panel.profile.edit', ['tab' => 'security'])
            ->with('success', '2FA aktif! Simpan kode pemulihan di tempat aman.')
            ->with('flash_data', ['recovery_codes' => $codes]);
    }

    public function recovery(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);
        abort_unless($request->user()->hasTwoFactorEnabled(), 422);
        $codes = TwoFactor::generateRecoveryCodes();
        $request->user()->forceFill(['two_factor_recovery_codes' => $codes])->save();
        AuditLog::record('2fa_recovery_regenerated', 'Membuat ulang kode pemulihan 2FA');

        return back()->with('success', 'Kode pemulihan baru dibuat.')->with('flash_data', ['recovery_codes' => $codes]);
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);
        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ])->save();
        AuditLog::record('2fa_disabled', 'Menonaktifkan autentikasi dua faktor');

        return back()->with('warning', '2FA dinonaktifkan. Akun Anda kini hanya dilindungi password.');
    }
}
