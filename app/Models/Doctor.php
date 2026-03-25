<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'specialty',
        'city',
        'years_of_experience',
        'consultation_price',
        'available_days',
    ];

    protected $casts = [
        'available_days' => 'array', // Transforme automatiquement le JSON en tableau PHP
    ];
}
