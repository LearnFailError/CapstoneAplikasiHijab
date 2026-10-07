<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Tests\TestCase;

class ChatbotFeatureTest extends TestCase
{
    public function test_chatbot_can_recommend_products_for_customer_preferences(): void
    {
        $category = Category::create([
            'name' => 'Satin',
            'slug' => 'satin',
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'Hijab Satin Elegant',
            'description' => 'Bahan satin lembut dan cocok untuk acara formal.',
            'material' => 'Satin',
            'color' => 'Cream',
            'price' => 150000,
            'stock' => 12,
            'image' => null,
            'is_active' => true,
        ]);

        $response = $this->postJson('/chatbot/ask', [
            'message' => 'mau hijab untuk acara formal dan bahan adem',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'reply',
            'recommendations' => [
                '*' => ['id', 'name', 'price', 'url'],
            ],
        ]);
        $response->assertJsonPath('recommendations.0.name', 'Hijab Satin Elegant');
    }
}
