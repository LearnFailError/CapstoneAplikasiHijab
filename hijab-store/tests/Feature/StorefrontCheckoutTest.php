<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_and_product_detail_are_available_to_visitors(): void
    {
        $category = Category::create(['name' => 'Pashmina', 'slug' => 'pashmina']);
        $product = $this->createProduct($category);

        $this->get('/')
            ->assertOk()
            ->assertSee($product->name);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('Rp 75.000');
    }

    public function test_guest_can_checkout_and_stock_is_decremented(): void
    {
        $category = Category::create(['name' => 'Pashmina', 'slug' => 'pashmina']);
        $product = $this->createProduct($category, stock: 4);

        $response = $this->withSession(['cart' => [$product->id => 2]])
            ->post(route('checkout.store'), [
                'customer_name' => 'Ayu Pembeli',
                'email' => 'ayu@example.test',
                'phone' => '081234567890',
                'address' => 'Jl. Melati No. 2',
                'city' => 'Bandung',
                'postal_code' => '40123',
                'payment_method' => 'bank_transfer',
            ]);

        $order = Order::with('items')->firstOrFail();

        $response->assertRedirect(route('checkout.confirmation', $order->number));
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'customer_name' => 'Ayu Pembeli',
            'payment_method' => 'bank_transfer',
            'total' => 150000,
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'line_total' => 150000,
        ]);
        $this->assertSame(2, $product->fresh()->stock);
        $response->assertSessionMissing('cart');
        $this->get(route('checkout.confirmation', $order->number))->assertOk()->assertSee($order->number);
    }

    public function test_signed_in_buyer_order_is_attached_to_their_account(): void
    {
        $buyer = User::factory()->create(['is_admin' => false]);
        $category = Category::create(['name' => 'Pashmina', 'slug' => 'pashmina']);
        $product = $this->createProduct($category);

        $this->actingAs($buyer)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), [
                'customer_name' => $buyer->name,
                'phone' => '081234567890',
                'address' => 'Jl. Melati No. 2',
                'city' => 'Bandung',
                'postal_code' => '40123',
                'payment_method' => 'cod',
            ])->assertRedirect();

        $order = Order::firstOrFail();

        $this->assertSame($buyer->id, $order->user_id);
        $this->get(route('account.show'))
            ->assertOk()
            ->assertSee($order->number);
    }

    public function test_checkout_rejects_stale_cart_stock_without_creating_order(): void
    {
        $category = Category::create(['name' => 'Pashmina', 'slug' => 'pashmina']);
        $product = $this->createProduct($category, stock: 1);

        $this->withSession(['cart' => [$product->id => 2]])
            ->from(route('checkout.create'))
            ->post(route('checkout.store'), [
                'customer_name' => 'Ayu Pembeli',
                'phone' => '081234567890',
                'address' => 'Jl. Melati No. 2',
                'city' => 'Bandung',
                'postal_code' => '40123',
                'payment_method' => 'cod',
            ])
            ->assertRedirect(route('checkout.create'))
            ->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(1, $product->fresh()->stock);
    }

    public function test_confirmation_is_only_available_in_the_session_that_created_the_order(): void
    {
        $order = Order::create([
            'number' => 'HJ-PRIVATE123',
            'customer_name' => 'Pembeli',
            'phone' => '081234567890',
            'address' => 'Alamat',
            'city' => 'Bandung',
            'postal_code' => '40123',
            'payment_method' => 'cod',
            'subtotal' => 1000,
            'total' => 1000,
        ]);

        $this->get(route('checkout.confirmation', $order->number))->assertNotFound();
    }

    public function test_search_rejects_unrecognized_filter_values(): void
    {
        $this->from(route('products.search'))
            ->get(route('products.search', ['material' => 'silk` && is_active:=0']))
            ->assertRedirect(route('products.search'))
            ->assertSessionHasErrors('material');
    }

    private function createProduct(Category $category, int $stock = 10): Product
    {
        return Product::create([
            'category_id' => $category->id,
            'name' => 'Pashmina Premium',
            'description' => 'Bahan lembut',
            'material' => 'Voal',
            'color' => 'Mocca',
            'price' => 75000,
            'stock' => $stock,
            'is_active' => true,
        ]);
    }
}
