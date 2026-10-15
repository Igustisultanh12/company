<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Setting;
use App\Support\TwoFactor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class AuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_success_and_failure(): void
    {
        $user = $this->agent(['email' => 'agen@test.id']);

        $this->post('/masuk', ['email' => 'agen@test.id', 'password' => 'salah'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertDatabaseHas('audit_logs', ['action' => 'login_failed']);

        $this->post('/masuk', ['email' => 'agen@test.id', 'password' => 'Password123'])->assertRedirect(route('agent.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_inertia_login_uses_full_reload(): void
    {
        $this->admin(['email' => 'admin@test.id']);
        $this->post('/masuk', ['email' => 'admin@test.id', 'password' => 'Password123'], ['X-Inertia' => 'true'])
            ->assertStatus(409)->assertHeader('X-Inertia-Location', route('admin.dashboard'));
    }

    public function test_login_is_rate_limited(): void
    {
        $this->agent(['email' => 'rl@test.id']);
        for ($i = 0; $i < 5; $i++) {
            $this->post('/masuk', ['email' => 'rl@test.id', 'password' => 'x']);
        }
        $res = $this->post('/masuk', ['email' => 'rl@test.id', 'password' => 'Password123']);
        $res->assertSessionHasErrors('email');
        $this->assertStringContainsString('Terlalu banyak', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login_and_is_kicked_out(): void
    {
        $user = $this->agent(['email' => 'off@test.id', 'is_active' => false]);
        $this->post('/masuk', ['email' => 'off@test.id', 'password' => 'Password123'])->assertSessionHasErrors('email');

        $active = $this->agent();
        $this->actingAs($active);
        $active->update(['is_active' => false]);
        $this->get('/agen')->assertRedirect(route('login'));
    }

    public function test_two_factor_login_flow(): void
    {
        $secret = TwoFactor::generateSecret();
        $user = $this->admin(['email' => 'twofa@test.id']);
        $user->forceFill(['two_factor_secret' => $secret, 'two_factor_confirmed_at' => now(), 'two_factor_recovery_codes' => ['aaaaa-bbbbb']])->save();

        $this->post('/masuk', ['email' => 'twofa@test.id', 'password' => 'Password123'])->assertRedirect(route('login.2fa'));
        $this->assertGuest();
        $this->post('/masuk/2fa', ['code' => '000000'])->assertSessionHasErrors('code');

        $code = (new Google2FA)->getCurrentOtp($secret);
        $this->post('/masuk/2fa', ['code' => $code])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_recovery_code_is_single_use(): void
    {
        $user = $this->admin(['email' => 'rec@test.id']);
        $user->forceFill(['two_factor_secret' => TwoFactor::generateSecret(), 'two_factor_confirmed_at' => now(), 'two_factor_recovery_codes' => ['abcde-12345']])->save();
        $this->post('/masuk', ['email' => 'rec@test.id', 'password' => 'Password123']);
        $this->post('/masuk/2fa', ['recovery_code' => 'abcde-12345'])->assertRedirect();
        $this->assertSame([], $user->fresh()->two_factor_recovery_codes);
    }

    public function test_role_based_access(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));

        $agent = $this->agent();
        $this->actingAs($agent)->get('/admin')->assertForbidden();
        $this->actingAs($agent)->get('/admin/pengguna')->assertForbidden();
        $this->actingAs($agent)->get('/agen')->assertOk();

        $admin = $this->admin();
        $this->actingAs($admin)->get('/agen')->assertForbidden();
        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/dashboard')->assertRedirect(route('admin.dashboard'));
    }

    public function test_force_admin_two_factor_setting(): void
    {
        $this->seed();
        Setting::put('force_admin_2fa', true);
        $admin = $this->admin();
        $this->actingAs($admin)->get('/admin')->assertRedirect(route('panel.profile.edit', ['tab' => 'security']));
        $this->actingAs($admin)->get('/panel/profil')->assertOk();
    }

    public function test_enable_two_factor_from_profile(): void
    {
        $user = $this->agent();
        $this->actingAs($user)->post('/panel/profil/2fa', ['password' => 'Password123'])->assertRedirect();
        $secret = session('2fa_setup_secret');
        $this->assertNotEmpty($secret);
        $this->actingAs($user)->post('/panel/profil/2fa/konfirmasi', ['code' => (new Google2FA)->getCurrentOtp($secret)])->assertSessionHas('flash_data');
        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());
        $this->assertCount(8, $user->fresh()->two_factor_recovery_codes);
    }

    public function test_password_change_requires_current_password_and_strength(): void
    {
        $user = $this->agent();
        $this->actingAs($user)->put('/panel/profil/password', ['current_password' => 'salah', 'password' => 'NewPass123', 'password_confirmation' => 'NewPass123'])->assertSessionHasErrors('current_password');
        $this->actingAs($user)->put('/panel/profil/password', ['current_password' => 'Password123', 'password' => 'lemah', 'password_confirmation' => 'lemah'])->assertSessionHasErrors('password');
        $this->actingAs($user)->put('/panel/profil/password', ['current_password' => 'Password123', 'password' => 'NewPass123', 'password_confirmation' => 'NewPass123'])->assertSessionHas('success');
    }

    public function test_logout(): void
    {
        $this->actingAs($this->agent())->post('/keluar')->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertTrue(AuditLog::where('action', 'logout')->exists());
    }
}
