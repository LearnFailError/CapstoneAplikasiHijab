<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function create(Request $request)
    {
        if ($request->session()->get('cart', []) === []) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Keranjang Anda masih kosong.']);
        }

        $cart = $request->session()->get('cart', []);
        $products = Product::query()->whereIn('id', array_keys($cart))->get()->keyBy('id');
        $items = collect($cart)->map(function (int $quantity, string $productId) use ($products): ?array {
            $product = $products->get($productId);

            return $product ? [
                'product' => $product,
                'quantity' => $quantity,
                'line_total' => (float) $product->price * $quantity,
            ] : null;
        })->filter();

        $buyer = $request->user()?->isAdmin() ? null : $request->user();

        return view('checkout.create', [
            'items' => $items,
            'subtotal' => $items->sum('line_total'),
            'buyer' => $buyer,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:2000'],
            'city' => ['required', 'string', 'max:120'],
            'postal_code' => ['required', 'string', 'max:20'],
            'payment_method' => ['required', 'in:cod,bank_transfer'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $cart = $request->session()->get('cart', []);

        if ($cart === []) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Keranjang Anda masih kosong.']);
        }

        $buyer = $request->user()?->isAdmin() ? null : $request->user();
        $validated['email'] ??= $buyer?->email;

        $order = DB::transaction(function () use ($validated, $cart, $buyer): Order {
            $products = Product::query()
                ->whereIn('id', array_keys($cart))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $subtotal = 0.0;

            foreach ($cart as $productId => $quantity) {
                $product = $products->get($productId);

                if (! $product || ! $product->is_active || $quantity > $product->stock) {
                    throw ValidationException::withMessages([
                        'cart' => 'Stok produk berubah atau produk tidak lagi tersedia. Silakan tinjau keranjang.',
                    ]);
                }

                $subtotal += (float) $product->price * $quantity;
            }

            $order = Order::create([
                ...$validated,
                'user_id' => $buyer?->id,
                'number' => 'HJ-'.Str::upper(Str::random(10)),
                'payment_status' => 'unpaid',
                'status' => 'pending',
                'subtotal' => $subtotal,
                'shipping_fee' => 0,
                'total' => $subtotal,
            ]);

            foreach ($cart as $productId => $quantity) {
                $product = $products->get($productId);
                $lineTotal = (float) $product->price * $quantity;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'line_total' => $lineTotal,
                ]);

                $product->decrement('stock', $quantity);
            }

            return $order;
        });

        $request->session()->forget('cart');
        $request->session()->put('last_order_id', $order->id);

        return redirect()->route('checkout.confirmation', $order->number);
    }

    public function confirmation(Request $request, Order $order)
    {
        abort_unless($request->session()->get('last_order_id') === $order->id, 404);

        return view('checkout.confirmation', ['order' => $order->load('items')]);
    }
}
