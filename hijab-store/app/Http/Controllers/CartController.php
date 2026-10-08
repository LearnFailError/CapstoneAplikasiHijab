<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        $products = Product::query()
            ->whereIn('id', array_keys($cart))
            ->where('is_active', true)
            ->get()
            ->keyBy('id');
        $items = collect($cart)->map(function (int $quantity, string $productId) use ($products): ?array {
            $product = $products->get($productId);

            return $product ? [
                'product' => $product,
                'quantity' => $quantity,
                'line_total' => (float) $product->price * $quantity,
            ] : null;
        })->filter();

        return view('cart.index', [
            'items' => $items,
            'subtotal' => $items->sum('line_total'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);
        $product = Product::query()->where('is_active', true)->findOrFail($validated['product_id']);
        $cart = $request->session()->get('cart', []);
        $quantity = ($cart[$product->id] ?? 0) + $validated['quantity'];

        if ($quantity > $product->stock) {
            return back()->withErrors(['quantity' => "Stok {$product->name} tersisa {$product->stock}."]);
        }

        $cart[$product->id] = $quantity;
        $request->session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('status', 'Produk ditambahkan ke keranjang.');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        if (! $product->is_active || $validated['quantity'] > $product->stock) {
            return back()->withErrors(['quantity' => 'Jumlah melebihi stok produk yang tersedia.']);
        }

        $cart = $request->session()->get('cart', []);
        $cart[$product->id] = $validated['quantity'];
        $request->session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('status', 'Keranjang diperbarui.');
    }

    public function destroy(Request $request, Product $product)
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$product->id]);
        $request->session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('status', 'Produk dihapus dari keranjang.');
    }
}
