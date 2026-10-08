<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyerAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_as_a_buyer_and_view_account(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Buat akun pembeli');

        $this->post(route('register.store'), [
            'name' => 'Ayu Pembeli',
            'email' => 'ayu@example.test',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
            'is_admin' => true,
        ])->assertRedirect(route('account.show'));

        $user = User::where('email', 'ayu@example.test')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertFalse($user->isAdmin());
        $this->get(route('account.show'))
            ->assertOk()
            ->assertSee('Halo, Ayu Pembeli')
            ->assertSee('Riwayat pesanan');
        $this->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_buyer_can_log_in_and_log_out(): void
    {
        $user = User::factory()->create([
            'email' => 'buyer@example.test',
            'password' => 'secure-password',
        ]);

        $this->get(route('login'))->assertOk()->assertSee('Masuk ke akun');
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'secure-password',
        ])->assertRedirect(route('account.show'));

        $this->assertAuthenticatedAs($user);
        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_admin_cannot_use_buyer_login_or_account(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.test',
            'password' => 'secure-password',
            'is_admin' => true,
        ]);

        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'secure-password',
        ])->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->actingAs($admin)
            ->get(route('account.show'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_signed_in_buyer_is_kept_out_of_admin_login(): void
    {
        $buyer = User::factory()->create(['is_admin' => false]);

        $this->actingAs($buyer)
            ->get(route('admin.login'))
            ->assertRedirect(route('account.show'));

        $this->post(route('admin.logout'))->assertForbidden();
        $this->assertAuthenticatedAs($buyer);
    }
}