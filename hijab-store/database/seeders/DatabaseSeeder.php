<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            'Pashmina' => 'pashmina',
            'Segi Empat' => 'segi-empat',
            'Instant' => 'instant',
        ])->map(fn (string $slug, string $name) => Category::updateOrCreate(
            ['slug' => $slug],
            ['name' => $name],
        ));

        $products = [
            ['Pashmina', 'Pashmina Voal Premium', 'Voal', 'Mocca', 69000, 18, 'Ringan, mudah dibentuk, dan nyaman dipakai sepanjang hari.'],
            ['Pashmina', 'Pashmina Ceruty Babydoll', 'Ceruty', 'Dusty Rose', 79000, 12, 'Tekstur lembut dengan jatuh kain yang anggun.'],
            ['Pashmina', 'Pashmina Jersey Daily', 'Jersey', 'Hitam', 59000, 20, 'Bahan lentur yang praktis untuk aktivitas harian.'],
            ['Segi Empat', 'Hijab Segi Empat Paris', 'Voal', 'Broken White', 49000, 24, 'Klasik dan mudah dipadukan dengan berbagai gaya.'],
            ['Segi Empat', 'Hijab Segi Empat Motif', 'Voal', 'Sage', 85000, 10, 'Motif lembut untuk tampilan istimewa.'],
            ['Segi Empat', 'Hijab Katun Premium', 'Katun', 'Navy', 65000, 14, 'Serat katun sejuk dengan warna yang tahan lama.'],
            ['Instant', 'Bergo Sport', 'Jersey', 'Abu-abu', 55000, 16, 'Praktis dan nyaman untuk perjalanan maupun olahraga ringan.'],
            ['Instant', 'Bergo Rayon Daily', 'Rayon', 'Olive', 62000, 11, 'Bahan rayon lembut dengan desain simpel.'],
        ];

        foreach ($products as [$categoryName, $name, $material, $color, $price, $stock, $description]) {
            Product::updateOrCreate(
                ['name' => $name],
                [
                    'category_id' => $categories->get($categoryName)->id,
                    'description' => $description,
                    'material' => $material,
                    'color' => $color,
                    'price' => $price,
                    'stock' => $stock,
                    'is_active' => true,
                ],
            );
        }

        $adminEmail = env('ADMIN_EMAIL');
        $adminPassword = env('ADMIN_PASSWORD');

        if ($adminEmail && $adminPassword) {
            User::updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => 'Admin Hijab Store',
                    'password' => $adminPassword,
                    'is_admin' => true,
                ],
            );
        }
    }
}
