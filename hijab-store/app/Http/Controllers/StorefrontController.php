<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\GeminiProductAssistant;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    public function index()
    {
        return view('welcome', [
            'categories' => Category::query()->withCount([
                'products' => fn ($query) => $query->where('is_active', true),
            ])->orderBy('name')->get(),
            'products' => Product::query()
                ->where('is_active', true)
                ->with('category')
                ->latest()
                ->paginate(8),
        ]);
    }

    public function chatbot(Request $request)
    {
        $message = trim((string) $request->input('message', ''));

        if ($message === '') {
            return response()->json([
                'reply' => 'Silakan sampaikan kebutuhanmu, misalnya “mau hijab untuk acara formal” atau “butuh bahan adem untuk harian”.',
                'recommendations' => [],
            ], 422);
        }

        $prompt = strtolower($message);
        $products = Product::query()->where('is_active', true)->with('category')->get();

        $weightMap = [
            'formal' => ['satin', 'silk', 'premium', 'wedding', 'acara'],
            'sehari' => ['cotton', 'katun', 'daily', 'harian', 'mudah'],
            'adem' => ['cotton', 'katun', 'linen', 'nyaman', 'sejuk'],
            'lebar' => ['besar', 'oversize', 'wider', 'panjang'],
            'motif' => ['motif', 'floral', 'pattern', 'unik'],
            'cerah' => ['cream', 'beige', 'putih', 'soft', 'pastel'],
            'gelap' => ['hitam', 'navy', 'charcoal', 'dark'],
        ];

        $matchedProducts = $products
            ->map(function (Product $product) use ($prompt, $weightMap) {
                $score = 0;
                $haystack = strtolower(sprintf('%s %s %s %s %s', $product->name, $product->description ?? '', $product->material ?? '', $product->color ?? '', $product->category?->name ?? ''));

                foreach ($weightMap as $intent => $keywords) {
                    if (str_contains($prompt, $intent)) {
                        foreach ($keywords as $keyword) {
                            if (str_contains($haystack, strtolower($keyword))) {
                                $score += 2;
                            }
                        }
                    }
                }

                foreach (['satin', 'katun', 'cotton', 'mesh', 'linen', 'ceruti', 'plisket'] as $keyword) {
                    if (str_contains($prompt, $keyword) && str_contains($haystack, $keyword)) {
                        $score += 3;
                    }
                }

                if (str_contains($prompt, 'formal') && str_contains($haystack, 'formal')) {
                    $score += 4;
                }

                if (str_contains($prompt, 'lihat') || str_contains($prompt, 'rekomendasi')) {
                    $score += 1;
                }

                return ['product' => $product, 'score' => $score];
            })
            ->sortByDesc('score')
            ->values();

        $recommendations = $matchedProducts->take(3)->map(fn ($item) => $item['product'])
            ->whenEmpty(fn () => $products->take(3));

        $assistant = new GeminiProductAssistant();
        $reply = $assistant->generateReply($message, $recommendations->all());

        $response = [
            'reply' => $reply,
            'recommendations' => $recommendations->map(function (Product $product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'url' => route('products.show', $product),
                ];
            })->values()->all(),
        ];

        return response()->json($response);
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);

        return view('products.show', [
            'product' => $product->load('category'),
            'relatedProducts' => Product::query()
                ->where('category_id', $product->category_id)
                ->where('is_active', true)
                ->whereKeyNot($product->getKey())
                ->with('category')
                ->latest()
                ->limit(4)
                ->get(),
        ]);
    }
}
