<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    /** Whitelist kunci per grup + aturan validasi. */
    private function groups(): array
    {
        $s = ['nullable', 'string', 'max:255'];
        $t = ['nullable', 'string', 'max:3000'];
        $u = ['nullable', 'url:https,http', 'max:255'];
        $b = ['nullable', 'boolean'];

        return [
            'company' => [
                'company_name' => ['required', 'string', 'max:150'],
                'company_short_name' => ['required', 'string', 'max:60'],
                'company_legal_type' => $s,
                'company_tagline' => $s,
                'company_description' => $t,
                'company_vision' => $t,
                'company_mission' => $t,
                'founder_name' => $s,
                'founder_position' => $s,
            ],
            'contact' => [
                'address' => ['nullable', 'string', 'max:500'],
                'city' => $s,
                'email' => ['nullable', 'email', 'max:150'],
                'phone' => ['nullable', 'string', 'max:30'],
                'whatsapp' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\s\-]*$/'],
                'office_hours' => $s,
                'maps_embed_url' => ['nullable', 'url:https', 'max:1000', 'regex:#^https://(www\.)?google\.com/maps#'],
                'instagram' => $u, 'tiktok' => $u, 'linkedin' => $u, 'github' => $u, 'youtube' => $u,
            ],
            'legal' => [
                'legal_sk_number' => $s, 'legal_sk_date' => ['nullable', 'date'], 'legal_sk_title' => $s,
                'legal_nib' => ['nullable', 'string', 'max:30'], 'legal_certificate_number' => ['nullable', 'string', 'max:40'],
                'legal_certificate_date' => ['nullable', 'date'], 'legal_risk_level' => $s, 'legal_sppl_status' => $s,
                'legal_kbli' => ['nullable', 'array', 'max:20'],
                'legal_kbli.*.code' => ['required', 'string', 'max:10'],
                'legal_kbli.*.title' => ['required', 'string', 'max:200'],
            ],
            'seo' => [
                'seo_title' => $s, 'seo_description' => ['nullable', 'string', 'max:300'], 'seo_keywords' => ['nullable', 'string', 'max:500'],
                'stat_projects' => ['nullable', 'integer', 'min:0'], 'stat_clients' => ['nullable', 'integer', 'min:0'],
                'stat_years' => ['nullable', 'integer', 'min:0'], 'stat_satisfaction' => ['nullable', 'integer', 'min:0', 'max:100'],
            ],
            'attendance' => [
                'attendance_start' => ['required', 'date_format:H:i'],
                'attendance_end' => ['required', 'date_format:H:i'],
                'attendance_late_tolerance' => ['required', 'integer', 'min:0', 'max:240'],
                'attendance_require_location' => $b,
                'attendance_office_lat' => ['nullable', 'numeric', 'between:-90,90'],
                'attendance_office_lng' => ['nullable', 'numeric', 'between:-180,180'],
                'attendance_radius_m' => ['nullable', 'integer', 'min:0', 'max:100000'],
            ],
            'invoice' => [
                'invoice_prefix' => ['required', 'string', 'max:10', 'alpha_dash'],
                'invoice_bank_name' => $s, 'invoice_bank_account' => ['nullable', 'string', 'max:40'], 'invoice_bank_holder' => $s,
                'invoice_footer' => ['nullable', 'string', 'max:500'],
                'invoice_tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            ],
            'security' => [
                'force_admin_2fa' => $b,
                'notify_email_new_order' => $b,
            ],
        ];
    }

    public function index(Request $request): Response
    {
        $values = [];
        foreach ($this->groups() as $keys) {
            foreach (array_keys($keys) as $k) {
                if (str_contains($k, '.')) {
                    continue;
                }
                $values[$k] = Setting::get($k, '');
            }
        }
        $values['legal_kbli'] = json_decode((string) Setting::get('legal_kbli', '[]'), true) ?: [];
        foreach (['attendance_require_location', 'force_admin_2fa', 'notify_email_new_order'] as $bk) {
            $values[$bk] = Setting::bool($bk);
        }

        return Inertia::render('Admin/Settings/Index', [
            'values' => $values,
            'tab' => (string) $request->query('tab', 'company'),
            'system' => [
                'php' => PHP_VERSION,
                'laravel' => app()->version(),
                'env' => app()->environment(),
                'debug' => (bool) config('app.debug'),
                'https' => str_starts_with((string) config('app.url'), 'https://'),
                'mail' => config('mail.default'),
                'qpdf' => $this->binaryAvailable('qpdf'),
                'gs' => $this->binaryAvailable('gs'),
                'storage_link' => is_link(public_path('storage')) || is_dir(public_path('storage')),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $group = (string) $request->input('group');
        $rules = $this->groups()[$group] ?? null;
        abort_unless($rules, 422);

        $data = $request->validate($rules, [
            'maps_embed_url.regex' => 'Gunakan URL embed dari Google Maps (https://www.google.com/maps/embed?...).',
        ]);

        $save = [];
        foreach (array_keys($rules) as $k) {
            if (str_contains($k, '.')) {
                continue;
            }
            $v = $data[$k] ?? null;
            if ($k === 'legal_kbli') {
                $v = json_encode(array_values($v ?? []));
            } elseif (in_array($k, ['attendance_require_location', 'force_admin_2fa', 'notify_email_new_order'], true)) {
                $v = $request->boolean($k);
            }
            $save[$k] = $v ?? '';
        }
        Setting::putMany($save);
        AuditLog::record('settings_updated', 'Memperbarui pengaturan: '.$group);

        return back()->with('success', 'Pengaturan disimpan.');
    }

    public function clearCache(): RedirectResponse
    {
        Cache::forget(Setting::CACHE_KEY);
        try {
            Artisan::call('view:clear');
            if (app()->isProduction()) {
                Artisan::call('config:cache');
                Artisan::call('route:cache');
                Artisan::call('view:cache');
            }
        } catch (\Throwable $e) {
            report($e);

            return back()->with('warning', 'Cache pengaturan dibersihkan, namun optimasi gagal: '.$e->getMessage());
        }
        AuditLog::record('cache_cleared', 'Membersihkan & membangun ulang cache');

        return back()->with('success', 'Cache dibersihkan & dioptimasi ulang.');
    }

    private function binaryAvailable(string $bin): bool
    {
        if (! function_exists('exec')) {
            return false;
        }
        $out = [];
        $code = 1;
        @exec('command -v '.escapeshellarg($bin).' 2>/dev/null', $out, $code);

        return $code === 0 && ! empty($out);
    }
}
