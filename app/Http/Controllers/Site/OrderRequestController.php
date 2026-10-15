<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Package;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserNotification;
use App\Support\Catalog;
use App\Support\SpamGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OrderRequestController extends Controller
{
    public function create(Request $request): Response
    {
        $ref = strtoupper(trim((string) $request->query('ref', '')));
        $agent = null;
        if ($ref !== '' && preg_match('/^[A-Z0-9]{4,20}$/', $ref)) {
            $agent = User::query()->where('referral_code', $ref)->where('role', User::ROLE_AGENT)->where('is_active', true)->first();
            if ($agent) {
                $request->session()->put('referral_code', $ref);
            }
        }
        if (! $agent && $request->session()->has('referral_code')) {
            $agent = User::query()->where('referral_code', $request->session()->get('referral_code'))->where('is_active', true)->first();
        }

        return Inertia::render('Site/Order/Create', [
            'meta' => [
                'title' => 'Request Website – '.Setting::get('company_name'),
                'description' => 'Ceritakan website impianmu: jenis, fitur, budget & tenggat. Konsultasi gratis!',
            ],
            'websiteTypes' => Catalog::WEBSITE_TYPES,
            'features' => Catalog::WEBSITE_FEATURES,
            'budgets' => Catalog::BUDGET_RANGES,
            'pagesEstimates' => Catalog::PAGES_ESTIMATES,
            'packages' => Package::query()->active()->get(['id', 'name', 'slug', 'price', 'price_note', 'description', 'color', 'is_popular', 'features', 'delivery_time']),
            'selectedPackage' => (string) $request->query('paket', ''),
            'selectedType' => (string) $request->query('jenis', ''),
            'referrer' => $agent ? ['name' => $agent->name, 'code' => $agent->referral_code] : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        SpamGuard::check($request);

        $data = $request->validate([
            'client_name' => ['required', 'string', 'max:100'],
            'client_email' => ['required', 'email:rfc', 'max:150'],
            'client_phone' => ['required', 'string', 'regex:/^[0-9+\-\s()]{8,30}$/'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'website_type' => ['required', Rule::in(array_keys(Catalog::WEBSITE_TYPES))],
            'package_id' => ['nullable', 'integer', Rule::exists('packages', 'id')->where('is_active', true)],
            'features' => ['nullable', 'array', 'max:30'],
            'features.*' => ['string', Rule::in(array_keys(Catalog::WEBSITE_FEATURES))],
            'pages_estimate' => ['nullable', Rule::in(array_keys(Catalog::PAGES_ESTIMATES))],
            'budget_range' => ['nullable', Rule::in(array_keys(Catalog::BUDGET_RANGES))],
            'deadline' => ['nullable', 'date', 'after:today'],
            'reference_urls' => ['nullable', 'string', 'max:1000'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'color_preference' => ['nullable', 'string', 'max:100'],
            'has_domain' => ['boolean'],
            'has_hosting' => ['boolean'],
            'agree' => ['accepted'],
            'referral_code' => ['nullable', 'string', 'max:20'],
        ], [
            'agree.accepted' => 'Mohon setujui pernyataan sebelum mengirim.',
            'description.min' => 'Ceritakan kebutuhanmu minimal 20 karakter ya.',
        ], [
            'client_name' => 'nama', 'client_email' => 'email', 'client_phone' => 'nomor WhatsApp',
            'website_type' => 'jenis website', 'description' => 'deskripsi kebutuhan', 'deadline' => 'tenggat',
        ]);

        $refCode = strtoupper((string) ($data['referral_code'] ?? $request->session()->get('referral_code', '')));
        $agent = $refCode !== ''
            ? User::query()->where('referral_code', $refCode)->where('role', User::ROLE_AGENT)->where('is_active', true)->first()
            : null;

        $order = Order::query()->create([
            'code' => Order::generateCode(),
            'client_name' => $data['client_name'],
            'client_email' => Str::lower($data['client_email']),
            'client_phone' => $data['client_phone'],
            'company_name' => $data['company_name'] ?? null,
            'website_type' => $data['website_type'],
            'package_id' => $data['package_id'] ?? null,
            'features' => array_values(array_unique($data['features'] ?? [])),
            'pages_estimate' => $data['pages_estimate'] ?? null,
            'budget_range' => $data['budget_range'] ?? null,
            'deadline' => $data['deadline'] ?? null,
            'reference_urls' => $data['reference_urls'] ?? null,
            'description' => $data['description'],
            'color_preference' => $data['color_preference'] ?? null,
            'has_domain' => (bool) ($data['has_domain'] ?? false),
            'has_hosting' => (bool) ($data['has_hosting'] ?? false),
            'status' => 'new',
            'priority' => 'normal',
            'referral_agent_id' => $agent?->id,
            'source' => $agent ? 'referral' : 'web',
            'ip_address' => $request->ip(),
        ]);

        $order->log('Permintaan website diterima. Tim kami akan meninjau dan menghubungi Anda maksimal 1x24 jam kerja.', 'system', true, null);

        $url = route('admin.orders.show', $order);
        UserNotification::toAdmins('Request website baru: '.$order->client_name, $order->type_label.' • '.$order->code, $url, 'success');
        if ($agent) {
            UserNotification::send($agent->id, 'Klien referral kamu mengirim request! 🎉', $order->client_name.' • '.$order->code, route('agent.orders.show', $order), 'success');
        }

        if (Setting::bool('notify_email_new_order') && ($to = Setting::get('email'))) {
            try {
                Mail::raw("Request website baru\n\nKode: {$order->code}\nNama: {$order->client_name}\nEmail: {$order->client_email}\nWA: {$order->client_phone}\nJenis: {$order->type_label}\n\n{$order->description}\n\nDetail: {$url}", function ($m) use ($to, $order) {
                    $m->to($to)->subject('[Request Website] '.$order->code.' – '.$order->client_name);
                });
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $request->session()->forget('referral_code');
        $request->session()->put('order_success_code', $order->code);
        $tracked = (array) $request->session()->get('tracked_orders', []);
        $tracked[] = $order->id;
        $request->session()->put('tracked_orders', array_values(array_unique($tracked)));

        return redirect()->route('order.success');
    }

    public function success(Request $request): Response|RedirectResponse
    {
        $code = $request->session()->get('order_success_code');
        $order = $code ? Order::query()->where('code', $code)->first() : null;
        if (! $order) {
            return redirect()->route('order.create');
        }

        return Inertia::render('Site/Order/Success', [
            'meta' => ['title' => 'Request Terkirim – '.Setting::get('company_name'), 'description' => ''],
            'order' => [
                'code' => $order->code,
                'client_name' => $order->client_name,
                'client_email' => $order->client_email,
                'type_label' => $order->type_label,
            ],
        ]);
    }
}
