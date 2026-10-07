<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Throwable;

class GeminiProductAssistant
{
    public function generateReply(string $message, iterable $products): string
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model', 'gemini-2.0-flash');

        if (blank($apiKey)) {
            return $this->fallbackReply($message, $products);
        }

        $catalog = collect($products)
            ->take(5)
            ->map(function ($product) {
                $name = data_get($product, 'name') ?? data_get($product, 'product.name') ?? 'Produk';
                $material = data_get($product, 'material') ?? data_get($product, 'product.material') ?? '';
                $color = data_get($product, 'color') ?? data_get($product, 'product.color') ?? '';
                $description = data_get($product, 'description') ?? data_get($product, 'product.description') ?? '';
                $price = data_get($product, 'price') ?? data_get($product, 'product.price') ?? 0;

                return sprintf(
                    '- %s | bahan: %s | warna: %s | harga: Rp %s | deskripsi: %s',
                    $name,
                    $material ?: 'tidak disebutkan',
                    $color ?: 'tidak disebutkan',
                    number_format((float) $price, 0, ',', '.'),
                    $description ?: 'produk pilihan yang nyaman dan trendy'
                );
            })
            ->implode("\n");

        $prompt = "Kamu adalah asisten penjualan untuk toko hijab. Balas dalam bahasa Indonesia.
Bantu pelanggan berikut: \"{$message}\".
Berikan rekomendasi yang singkat, ramah, dan menarik.
Gunakan katalog produk berikut sebagai referensi:\n{$catalog}\n
Instruksi penting: jangan menulis angka atau format JSON. Jawab hanya dalam kalimat yang mudah dibaca, ringkas, dan cocok untuk pelanggan online.";

        try {
            $response = Http::timeout(20)->post(
                'https://generativelanguage.googleapis.com/v1beta/models/'.$model.':generateContent?key='.$apiKey,
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 250,
                    ],
                ]
            );

            $text = data_get($response->json(), 'candidates.0.content.parts.0.text');

            if (is_string($text) && trim($text) !== '') {
                return trim($text);
            }
        } catch (Throwable $e) {
            // fallback intentionally kept for local/offline environments
        }

        return $this->fallbackReply($message, $products);
    }

    protected function fallbackReply(string $message, iterable $products): string
    {
        $catalog = collect($products)->take(3);

        if ($catalog->isEmpty()) {
            return 'Saya belum menemukan produk yang cocok untuk kebutuhanmu saat ini. Coba pilih model yang lebih spesifik seperti “formal”, “adem”, atau “motif”.';
        }

        $first = $catalog->first();
        $name = data_get($first, 'name') ?? data_get($first, 'product.name') ?? 'produk unggulan';
        $prompt = strtolower($message);

        if (str_contains($prompt, 'formal') || str_contains($prompt, 'acara')) {
            return 'Rekomendasi terbaik saya adalah '.$name.' karena tampilannya elegan dan cocok untuk acara formal. Produk ini juga nyaman dipakai sepanjang hari.';
        }

        if (str_contains($prompt, 'adem') || str_contains($prompt, 'harian')) {
            return 'Untuk kebutuhan harian yang nyaman, saya sarankan '.$name.'. Bahan dan fiturnya cocok untuk aktivitas sehari-hari dengan tampilan yang tetap stylish.';
        }

        if (str_contains($prompt, 'motif') || str_contains($prompt, 'unik')) {
            return 'Saya rekomendasikan '.$name.' untuk tampilan yang lebih ekspresif dan menarik. Pilihan ini memberi sentuhan unik tanpa mengorbankan kenyamanan.';
        }

        return 'Saya sarankan '.$name.' sebagai opsi yang paling cocok dengan kebutuhanmu. Anda juga bisa lihat koleksi lain di katalog kami untuk pilihan yang lebih sesuai.';
    }
}
