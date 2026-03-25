<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Doctor;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Doctor::create([
            'name' => 'Dr. Dupont Jean',
            'specialty' => 'Cardiologue',
            'city' => 'Paris',
            'years_of_experience' => 15,
            'consultation_price' => 80.00,
            'available_days' => ['Lundi', 'Mercredi', 'Vendredi'],
        ]);

        Doctor::create([
            'name' => 'Dr. Martin Sophie',
            'specialty' => 'Généraliste',
            'city' => 'Lyon',
            'years_of_experience' => 8,
            'consultation_price' => 50.00,
            'available_days' => ['Mardi', 'Jeudi', 'Samedi'],
        ]);
    }
}
