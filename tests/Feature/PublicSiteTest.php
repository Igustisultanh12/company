<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Post;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_public_pages_render(): void
    {
        $pages = [
            '/' => 'Site/Home', '/tentang' => 'Site/About', '/layanan' => 'Site/Services/Index', '/harga' => 'Site/Pricing',
            '/portofolio' => 'Site/Portfolio/Index', '/blog' => 'Site/Blog/Index', '/kontak' => 'Site/Contact',
            '/request-website' => 'Site/Order/Create', '/lacak' => 'Site/Track/Index', '/verifikasi' => 'Site/Verify',
        ];
        foreach ($pages as $url => $component) {
            $this->get($url)->assertOk()->assertInertia(fn (Assert $p) => $p->component($component)->has('site')->where('site.nib', '0109260072761'));
        }

        $service = Service::query()->first();
        $this->get('/layanan/'.$service->slug)->assertOk()->assertInertia(fn (Assert $p) => $p->component('Site/Services/Show'));
        $post = Post::query()->first();
        $this->get('/blog/'.$post->slug)->assertOk()->assertInertia(fn (Assert $p) => $p->component('Site/Blog/Show')->has('bodyHtml'));
        $this->get('/portofolio/website-pt-kreatif-sky-abadi')->assertOk();
        $this->get('/portofolio/sisinden')->assertNotFound(); // draf tidak tampil
        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $this->get('/halaman-tidak-ada')->assertNotFound();
    }

    public function test_home_contains_legal_data_from_documents(): void
    {
        $this->get('/')->assertInertia(fn (Assert $p) => $p
            ->where('legal.sk_number', 'AHU-A012478.AH.01.31.Tahun 2026')
            ->where('legal.certificate_number', '01092600727610001')
            ->has('legal.kbli', 3)
            ->has('services', 6)
            ->has('packages', 4));
    }

    public function test_security_headers_and_csp_nonce(): void
    {
        $res = $this->get('/');
        $res->assertHeader('X-Frame-Options', 'SAMEORIGIN')->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertMatchesRegularExpression("/script-src 'self' 'nonce-[A-Za-z0-9]+'/", $res->headers->get('Content-Security-Policy'));
        $this->assertStringNotContainsString('admin.dashboard', $res->getContent(), 'Rute admin tidak boleh bocor ke tamu');
    }

    public function test_contact_form_and_honeypot(): void
    {
        $this->post('/kontak', ['name' => 'Budi', 'email' => 'budi@example.com', 'message' => 'Halo saya ingin website', 'website' => 'spam'])
            ->assertSessionHasErrors('form');
        $this->assertSame(0, ContactMessage::count());

        $this->post('/kontak', ['name' => 'Budi', 'email' => 'budi@example.com', 'message' => 'Halo saya ingin website', '_started' => now()->subMinute()->getTimestampMs()])
            ->assertSessionHas('success');
        $this->assertSame(1, ContactMessage::count());
    }

    public function test_order_request_flow_with_tracking_and_referral(): void
    {
        $agent = User::query()->where('role', 'agent')->first() ?? $this->agent();
        $this->get('/request-website?ref='.$agent->referral_code)->assertInertia(fn (Assert $p) => $p->where('referrer.code', $agent->referral_code));

        $res = $this->post('/request-website', [
            'client_name' => 'Siti Aminah', 'client_email' => 'Siti@Example.com', 'client_phone' => '081234567890',
            'website_type' => 'online_store', 'features' => ['responsive', 'payment_gateway'], 'budget_range' => '5-10jt',
            'description' => 'Saya butuh toko online untuk usaha batik dengan pembayaran otomatis.', 'agree' => true,
            'has_domain' => false, 'has_hosting' => true, '_started' => now()->subMinute()->getTimestampMs(),
        ]);
        $res->assertRedirect(route('order.success'));
        $order = Order::query()->firstOrFail();
        $this->assertSame('siti@example.com', $order->client_email);
        $this->assertSame($agent->id, $order->referral_agent_id);
        $this->assertSame('referral', $order->source);

        $this->get(route('order.success'))->assertInertia(fn (Assert $p) => $p->component('Site/Order/Success')->where('order.code', $order->code));
        $this->get(route('track.show', $order->code))->assertOk()->assertInertia(fn (Assert $p) => $p->component('Site/Track/Show')->has('order.activities', 1));

        // Sesi baru: wajib email yang cocok
        $this->flushSession();
        $this->get(route('track.show', $order->code))->assertRedirect();
        $this->post('/lacak', ['code' => $order->code, 'email' => 'salah@example.com'])->assertSessionHasErrors('code');
        $this->post('/lacak', ['code' => $order->code, 'email' => 'siti@example.com'])->assertRedirect(route('track.show', $order->code));
    }

    public function test_order_validation(): void
    {
        $this->post('/request-website', ['_started' => now()->subMinute()->getTimestampMs()])
            ->assertSessionHasErrors(['client_name', 'client_email', 'client_phone', 'website_type', 'description', 'agree']);
    }
}
