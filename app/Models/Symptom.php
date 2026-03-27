<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Symptom extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'severity',
        'description',
        'date_recorded',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_recorded' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
