<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = $this->admin();
    }

    public function test_all_admin_pages_render(): void
    {
        $order = Order::query()->create(['code' => Order::generateCode(), 'client_name' => 'A', 'client_email' => 'a@a.id', 'client_phone' => '0812', 'website_type' => 'landing_page', 'description' => 'tes deskripsi panjang', 'status' => 'new']);
        $invoice = Invoice::query()->create(['number' => 'INV/X/1', 'client_name' => 'A', 'issue_date' => today(), 'items' => [['description' => 'x', 'qty' => 1, 'price' => 1000]], 'total' => 1000, 'status' => 'unpaid']);
        ContactMessage::query()->create(['name' => 'X', 'email' => 'x@x.id', 'message' => 'halo halo halo']);

        $urls = [
            '/admin', '/admin/pesanan', "/admin/pesanan/{$order->id}", '/admin/invoice', '/admin/invoice/buat', "/admin/invoice/buat?order={$order->id}",
            "/admin/invoice/{$invoice->id}", "/admin/invoice/{$invoice->id}/ubah", '/admin/pesan-masuk', '/admin/pengguna', '/admin/absensi',
            '/admin/absensi?mode=monthly', '/admin/cuti', '/admin/laporan-harian', '/admin/log-aktivitas', '/admin/pengaturan', '/admin/tte-pengaturan',
            '/admin/legalitas', '/panel/arsip', '/panel/tugas', '/panel/tte', '/panel/tte/ajukan', '/panel/profil', '/panel/notifikasi', '/panel/pengumuman',
        ];
        foreach (['services', 'packages', 'portfolios', 'posts', 'testimonials', 'faqs', 'team', 'announcements'] as $r) {
            $urls[] = "/admin/konten/{$r}";
        }
        foreach ($urls as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk();
        }
        $this->actingAs($this->admin)->get("/admin/invoice/{$invoice->id}/cetak")->assertOk()->assertSee('INV/X/1');
        $this->actingAs($this->admin)->get('/admin/pesanan/ekspor')->assertOk();
        $this->actingAs($this->admin)->get('/admin/absensi/ekspor')->assertOk();
        $this->actingAs($this->admin)->get('/admin/konten/tidak-ada')->assertNotFound();
    }

    public function test_cms_crud_with_image_upload(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin)->post('/admin/konten/portfolios', [
            'title' => 'Website Toko Kopi', 'client' => 'Kopi Mayang', 'category' => 'Toko Online', 'color' => 'orange',
            'tech_stack' => ['Laravel', 'Vue'], 'is_active' => '1', 'is_featured' => '0', 'sort_order' => 3,
            'image_path' => UploadedFile::fake()->image('shot.jpg', 1200, 800),
        ])->assertSessionHas('success');

        $p = Portfolio::query()->where('title', 'Website Toko Kopi')->firstOrFail();
        $this->assertSame('website-toko-kopi', $p->slug);
        $this->assertStringEndsWith('.webp', $p->image_path);
        Storage::disk('public')->assertExists($p->image_path);

        $this->actingAs($this->admin)->post("/admin/konten/portfolios/{$p->id}", ['title' => 'Website Toko Kopi 2', 'slug' => '', 'is_active' => '1', 'remove_image_path' => '1'])->assertSessionHas('success');
        $this->assertNull($p->fresh()->image_path);

        $this->actingAs($this->admin)->patch("/admin/konten/portfolios/{$p->id}/toggle")->assertSessionHas('success');
        $this->assertFalse($p->fresh()->is_active);

        $this->actingAs($this->admin)->delete("/admin/konten/portfolios/{$p->id}")->assertSessionHas('success');
        $this->assertNull($p->fresh());

        // validasi ikon/warna ditolak bila tidak ada di whitelist
        $this->actingAs($this->admin)->post('/admin/konten/services', ['title' => 'X', 'icon' => '<script>', 'color' => 'hitam'])->assertSessionHasErrors(['icon', 'color']);
    }

    public function test_order_management_and_invoice_payment(): void
    {
        $agent = $this->agent();
        $order = Order::query()->create(['code' => Order::generateCode(), 'client_name' => 'Rina', 'client_email' => 'rina@x.id', 'client_phone' => '0812', 'website_type' => 'company_profile', 'description' => 'Butuh company profile', 'status' => 'new']);

        $this->actingAs($this->admin)->put("/admin/pesanan/{$order->id}", [
            'client_name' => 'Rina', 'client_email' => 'rina@x.id', 'client_phone' => '0812', 'website_type' => 'company_profile', 'description' => 'Butuh company profile',
            'status' => 'in_progress', 'priority' => 'high', 'progress' => 40, 'assigned_to' => $agent->id, 'quoted_price' => 3500000, 'status_note' => 'Mulai dikerjakan',
        ])->assertSessionHas('success');
        $order->refresh();
        $this->assertSame('in_progress', $order->status);
        $this->assertNotNull($order->started_at);
        $this->assertTrue($order->activities()->where('type', 'status')->where('is_public', true)->exists());
        $this->assertDatabaseHas('user_notifications', ['user_id' => $agent->id]);

        $this->actingAs($this->admin)->post('/admin/invoice', [
            'order_id' => $order->id, 'client_name' => 'Rina', 'issue_date' => today()->toDateString(), 'due_date' => today()->addWeek()->toDateString(),
            'items' => [['description' => 'Company profile', 'qty' => 1, 'price' => 3500000], ['description' => 'Domain', 'qty' => 1, 'price' => 150000]],
            'discount' => 150000, 'tax_percent' => 11, 'status' => 'unpaid',
        ])->assertRedirect();
        $inv = Invoice::query()->latest('id')->firstOrFail();
        $this->assertSame(3650000, $inv->subtotal);
        $this->assertSame(385000, $inv->tax_amount);
        $this->assertSame(3885000, $inv->total);
        $this->assertMatchesRegularExpression('#^INV/\d{6}/0001$#', $inv->number);

        $this->actingAs($this->admin)->post("/admin/invoice/{$inv->id}/pembayaran", ['amount' => 2000000, 'paid_at' => today()->toDateString(), 'method' => 'Transfer Bank'])->assertSessionHas('success');
        $this->assertSame('partial', $inv->fresh()->status);
        $this->actingAs($this->admin)->post("/admin/invoice/{$inv->id}/pembayaran", ['amount' => 9999999, 'paid_at' => today()->toDateString(), 'method' => 'Transfer Bank'])->assertSessionHasErrors('amount');
        $this->actingAs($this->admin)->post("/admin/invoice/{$inv->id}/pembayaran", ['amount' => 1885000, 'paid_at' => today()->toDateString(), 'method' => 'QRIS'])->assertSessionHas('success');
        $this->assertSame('paid', $inv->fresh()->status);

        // tautan invoice publik bertanda tangan
        $url = URL::temporarySignedRoute('invoice.public', now()->addDay(), ['invoice' => $inv->id]);
        $this->get($url)->assertOk()->assertSee('Lunas');
        $this->get("/invoice/{$inv->id}/lihat")->assertForbidden();
    }

    public function test_user_management(): void
    {
        $this->actingAs($this->admin)->post('/admin/pengguna', ['name' => 'Agen Baru', 'email' => 'baru@test.id', 'role' => 'agent'])->assertSessionHas('flash_data');
        $u = User::query()->where('email', 'baru@test.id')->firstOrFail();
        $this->assertNotNull($u->referral_code);

        $this->actingAs($this->admin)->patch("/admin/pengguna/{$u->id}/status")->assertSessionHas('success');
        $this->assertFalse($u->fresh()->is_active);
        $this->actingAs($this->admin)->patch("/admin/pengguna/{$this->admin->id}/status")->assertSessionHas('error');
        $this->actingAs($this->admin)->delete("/admin/pengguna/{$this->admin->id}")->assertSessionHas('error');
    }

    public function test_settings_update_whitelist(): void
    {
        $this->actingAs($this->admin)->put('/admin/pengaturan', ['group' => 'contact', 'whatsapp' => '6281234567890', 'email' => 'halo@kreatifsky.id', 'maps_embed_url' => 'https://evil.com/x'])->assertSessionHasErrors('maps_embed_url');
        $this->actingAs($this->admin)->put('/admin/pengaturan', ['group' => 'contact', 'whatsapp' => '6281234567890', 'email' => 'halo@kreatifsky.id'])->assertSessionHas('success');
        $this->get('/')->assertInertia(fn (Assert $p) => $p->where('site.whatsapp', '6281234567890'));
        $this->actingAs($this->admin)->put('/admin/pengaturan', ['group' => 'hacker', 'APP_KEY' => 'x'])->assertStatus(422);
    }
}
