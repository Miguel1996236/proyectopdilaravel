<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quiz extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'opens_at',
        'closes_at',
        'max_attempts',
        'require_login',
        'target_audience',
        'settings',
        'analysis_requested_at',
        'analysis_completed_at',
    ];

    protected $casts = [
        'opens_at' => 'datetime',
        'closes_at' => 'datetime',
        'analysis_requested_at' => 'datetime',
        'analysis_completed_at' => 'datetime',
        'settings' => 'array',
        'require_login' => 'boolean',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('position');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(QuizInvitation::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(QuizAiAnalysis::class);
    }
}

