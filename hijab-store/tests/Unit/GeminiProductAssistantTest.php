<?php

namespace Tests\Unit;

use App\Services\GeminiProductAssistant;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiProductAssistantTest extends TestCase
{
    public function test_it_builds_a_reply_from_gemini_response(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Rekomendasi saya: Hijab Satin Elegant untuk acara formal.'],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        config()->set('services.gemini.api_key', 'test-key');
        config()->set('services.gemini.model', 'gemini-2.0-flash');

        $service = new GeminiProductAssistant();
        $reply = $service->generateReply('mau hijab formal', [
            ['name' => 'Hijab Satin Elegant', 'material' => 'Satin', 'color' => 'Cream', 'description' => 'Nyaman untuk acara formal'],
        ]);

        $this->assertStringContainsString('Hijab Satin Elegant', $reply);
        Http::assertSent(fn ($request) => str_contains((string) $request->url(), 'generativelanguage.googleapis.com'));
    }
}
