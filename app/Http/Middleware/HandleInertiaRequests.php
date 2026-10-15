<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();
        $s = Setting::allCached();
        $get = fn (string $k, $d = '') => ($s[$k] ?? null) !== null && $s[$k] !== '' ? $s[$k] : $d;

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'position' => $user->position,
                    'avatar_url' => $user->avatar_url,
                    'initials' => $user->initials,
                    'two_factor_enabled' => $user->hasTwoFactorEnabled(),
                ] : null,
            ],
            'unreadNotifications' => fn () => $user ? UserNotification::query()->where('user_id', $user->id)->whereNull('read_at')->count() : 0,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'data' => fn () => $request->session()->get('flash_data'),
            ],
            'site' => [
                'name' => $get('company_name', config('app.name')),
                'short_name' => $get('company_short_name', 'Kreatif Sky'),
                'legal_type' => $get('company_legal_type'),
                'tagline' => $get('company_tagline'),
                'email' => $get('email'),
                'phone' => $get('phone'),
                'whatsapp' => preg_replace('/\D/', '', (string) $get('whatsapp')),
                'address' => $get('address'),
                'city' => $get('city'),
                'office_hours' => $get('office_hours'),
                'nib' => $get('legal_nib'),
                'socials' => array_filter([
                    'instagram' => $get('instagram'),
                    'tiktok' => $get('tiktok'),
                    'linkedin' => $get('linkedin'),
                    'github' => $get('github'),
                    'youtube' => $get('youtube'),
                ]),
                'seo_title' => $get('seo_title'),
                'seo_description' => $get('seo_description'),
            ],
        ]);
    }
}
