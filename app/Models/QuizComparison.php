<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizComparison extends Model
{
    protected $fillable = [
        'user_id',
        'quiz_a_id',
        'quiz_b_id',
        'ai_analysis',
        'stats_a',
        'stats_b',
        'insights_a',
        'insights_b',
        'error_message',
        'analyzed_at',
    ];

    protected $casts = [
        'stats_a' => 'array',
        'stats_b' => 'array',
        'insights_a' => 'array',
        'insights_b' => 'array',
        'analyzed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quizA(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_a_id');
    }

    public function quizB(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_b_id');
    }
}
