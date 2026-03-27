<?php

namespace Tests\Feature;

use App\Models\Symptom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AiAdviceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_generate_and_list_ai_advice_history(): void
    {
        config(['services.gemini.api_key' => 'test-key']);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Symptom::create([
            'user_id' => $user->id,
            'name' => 'Headache',
            'severity' => 'moderate',
            'description' => 'Pain in the evening',
            'date_recorded' => now()->toDateString(),
        ]);

        Symptom::create([
            'user_id' => $user->id,
            'name' => 'Fatigue',
            'severity' => 'mild',
            'date_recorded' => now()->subDay()->toDateString(),
        ]);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Hydrate yourself, rest well, and consult a doctor if symptoms persist.'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->postJson('/api/ai/health-advice')
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.advice', 'Hydrate yourself, rest well, and consult a doctor if symptoms persist.')
            ->assertJsonCount(2, 'data.symptoms_analyzed');

        $this->getJson('/api/ai/health-advice/history')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.advice', 'Hydrate yourself, rest well, and consult a doctor if symptoms persist.');
    }

    public function test_ai_advice_requires_symptoms(): void
    {
        config(['services.gemini.api_key' => 'test-key']);

        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/ai/health-advice')
            ->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Vous n\'avez aucun symptôme enregistré à analyser.');
    }
}
