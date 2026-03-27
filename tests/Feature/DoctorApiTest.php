<?php

namespace Tests\Feature;

use App\Models\Doctor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_list_search_and_show_doctors(): void
    {
        $doctor = Doctor::create([
            'name' => 'Dr. Nadia',
            'specialty' => 'Cardiologue',
            'city' => 'Casablanca',
            'years_of_experience' => 11,
            'consultation_price' => 220,
            'available_days' => ['Monday', 'Thursday'],
        ]);

        $this->getJson('/api/doctors')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');

        $this->getJson('/api/doctors/search?specialty=Cardio&city=Casa')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson('/api/doctors/'.$doctor->id)
            ->assertOk()
            ->assertJsonPath('data.id', $doctor->id);
    }
}
