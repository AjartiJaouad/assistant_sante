<?php

namespace Tests\Feature;

use App\Models\Symptom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SymptomApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_crud_symptoms(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $createResponse = $this->postJson('/api/symptoms', [
            'name' => 'Fatigue',
            'severity' => 'moderate',
            'description' => 'Fatigue depuis deux jours',
            'date_recorded' => now()->toDateString(),
            'notes' => 'Survient le soir',
        ]);

        $createResponse
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Fatigue');

        $symptomId = $createResponse->json('data.id');

        $this->getJson("/api/symptoms/{$symptomId}")
            ->assertOk()
            ->assertJsonPath('data.id', $symptomId);

        $this->putJson("/api/symptoms/{$symptomId}", [
            'severity' => 'severe',
            'notes' => 'Douleur plus forte',
        ])
            ->assertOk()
            ->assertJsonPath('data.severity', 'severe')
            ->assertJsonPath('data.notes', 'Douleur plus forte');

        $this->getJson('/api/symptoms')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->deleteJson("/api/symptoms/{$symptomId}")
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_user_cannot_access_someone_elses_symptom(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $symptom = Symptom::create([
            'user_id' => $owner->id,
            'name' => 'Headache',
            'severity' => 'mild',
            'date_recorded' => now()->toDateString(),
        ]);

        Sanctum::actingAs($otherUser);

        $this->getJson("/api/symptoms/{$symptom->id}")
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Ressource introuvable');
    }

    public function test_symptom_validation_errors_use_unified_json(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/symptoms', [
            'severity' => 'invalid',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Erreur de validation')
            ->assertJsonStructure([
                'success',
                'errors' => ['name', 'severity', 'date_recorded'],
                'message',
            ]);
    }
}
