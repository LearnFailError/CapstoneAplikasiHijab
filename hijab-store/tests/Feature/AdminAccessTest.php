<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pages_require_an_admin_account(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_non_admin_user_cannot_log_in_to_the_admin_panel(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.test',
            'password' => 'secret-password',
        ]);

        $this->post(route('admin.login.store'), [
            'email' => $user->email,
            'password' => 'secret-password',
        ])->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_can_access_dashboard_and_create_categories(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Ringkasan toko');

        $this->post(route('admin.categories.store'), ['name' => 'Pashmina'])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Pashmina',
            'slug' => 'pashmina',
        ]);
    }

    public function test_admin_cannot_delete_category_that_contains_products(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create(['name' => 'Pashmina', 'slug' => 'pashmina']);
        $category->products()->create([
            'name' => 'Produk',
            'price' => 10000,
            'stock' => 1,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertSessionHasErrors('category');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_admin_can_create_and_manage_product_catalog(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create(['name' => 'Pashmina', 'slug' => 'pashmina']);

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'name' => 'Pashmina Voal',
                'material' => 'Voal',
                'color' => 'Mocca',
                'price' => 65000,
                'stock' => 5,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'category_id' => $category->id,
            'name' => 'Pashmina Voal',
            'stock' => 5,
            'is_active' => true,
        ]);
    }

    public function test_cancelling_an_order_restores_stock_only_once(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create(['name' => 'Pashmina', 'slug' => 'pashmina']);
        $product = $category->products()->create([
            'name' => 'Produk',
            'price' => 10000,
            'stock' => 3,
        ]);
        $order = Order::create([
            'number' => 'HJ-CANCEL123',
            'customer_name' => 'Pembeli',
            'phone' => '081234567890',
            'address' => 'Alamat',
            'city' => 'Bandung',
            'postal_code' => '40123',
            'payment_method' => 'cod',
            'subtotal' => 20000,
            'total' => 20000,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'unit_price' => 10000,
            'line_total' => 20000,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.orders.update', $order), [
                'status' => 'cancelled',
                'payment_status' => 'unpaid',
            ])
            ->assertRedirect(route('admin.orders.show', $order));

        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_only_cancelled_orders_can_be_deleted(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $order = Order::create([
            'number' => 'HJ-DELETE123',
            'customer_name' => 'Pembeli',
            'phone' => '081234567890',
            'address' => 'Alamat',
            'city' => 'Bandung',
            'postal_code' => '40123',
            'payment_method' => 'cod',
            'subtotal' => 10000,
            'total' => 10000,
        ]);
        $item = $order->items()->create([
            'product_name' => 'Produk',
            'quantity' => 1,
            'unit_price' => 10000,
            'line_total' => 10000,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.orders.destroy', $order))
            ->assertSessionHasErrors('order');
        $this->assertDatabaseHas('orders', ['id' => $order->id]);

        $order->update(['status' => 'cancelled']);
        $order->update(['payment_status' => 'paid']);

        $this->delete(route('admin.orders.destroy', $order))
            ->assertSessionHasErrors('order');
        $this->assertDatabaseHas('orders', ['id' => $order->id]);

        $order->update(['payment_status' => 'unpaid']);

        $this->delete(route('admin.orders.destroy', $order))
            ->assertRedirect(route('admin.orders.index'));
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertDatabaseMissing('order_items', ['id' => $item->id]);
    }
}
