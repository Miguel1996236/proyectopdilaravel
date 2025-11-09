<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAiAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'attempt_id',
        'type',
        'model',
        'summary',
        'payload',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'generated_at',
        'generated_by',
    ];

    protected $casts = [
        'payload' => 'array',
        'generated_at' => 'datetime',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}

