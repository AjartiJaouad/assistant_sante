<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AppointmentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_crud_appointments(): void
    {
        $user = User::factory()->create();
        $doctor = Doctor::create([
            'name' => 'Dr. House',
            'specialty' => 'Generaliste',
            'city' => 'Casablanca',
            'years_of_experience' => 12,
            'consultation_price' => 250,
            'available_days' => ['Monday', 'Tuesday'],
        ]);

        Sanctum::actingAs($user);

        $createResponse = $this->postJson('/api/appointments', [
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->addDay()->toISOString(),
            'notes' => 'Premiere consultation',
        ]);

        $createResponse
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.doctor.id', $doctor->id);

        $appointmentId = $createResponse->json('data.id');

        $this->getJson("/api/appointments/{$appointmentId}")
            ->assertOk()
            ->assertJsonPath('data.id', $appointmentId);

        $this->putJson("/api/appointments/{$appointmentId}", [
            'status' => 'confirmed',
            'notes' => 'Confirme par telephone',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'confirmed')
            ->assertJsonPath('data.notes', 'Confirme par telephone');

        $this->getJson('/api/appointments')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->deleteJson("/api/appointments/{$appointmentId}")
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_appointment_date_must_be_in_the_future(): void
    {
        $user = User::factory()->create();
        $doctor = Doctor::create([
            'name' => 'Dr. Strange',
            'specialty' => 'Neurologue',
            'city' => 'Rabat',
            'years_of_experience' => 9,
            'consultation_price' => 300,
            'available_days' => ['Friday'],
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/appointments', [
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->subDay()->toISOString(),
        ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Erreur de validation');
    }

    public function test_user_only_sees_their_own_appointments(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $doctor = Doctor::create([
            'name' => 'Dr. Grey',
            'specialty' => 'Cardiologue',
            'city' => 'Marrakech',
            'years_of_experience' => 15,
            'consultation_price' => 400,
            'available_days' => ['Wednesday'],
        ]);

        Appointment::create([
            'user_id' => $user->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->addDays(2),
            'status' => 'pending',
        ]);

        Appointment::create([
            'user_id' => $otherUser->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->addDays(3),
            'status' => 'pending',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/appointments')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
