<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAdvice extends Model
{
    use HasFactory;

    protected $table = 'ai_advices';

    protected $fillable = [
        'user_id',
        'advice',
        'symptoms_analyzed',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'symptoms_analyzed' => 'array',
            'generated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
