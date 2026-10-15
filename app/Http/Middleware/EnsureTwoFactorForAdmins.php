<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bila "Wajib 2FA untuk Admin" diaktifkan, admin tanpa 2FA diarahkan ke halaman keamanan.
 */
class EnsureTwoFactorForAdmins
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $user->isAdmin() && ! $user->hasTwoFactorEnabled() && Setting::bool('force_admin_2fa')
            && ! $request->routeIs('panel.profile.*', 'logout', 'panel.notifications.*')) {
            return redirect()->route('panel.profile.edit', ['tab' => 'security'])
                ->with('warning', 'Demi keamanan, aktifkan autentikasi dua faktor (2FA) terlebih dahulu.');
        }

        return $next($request);
    }
}
