<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'created_by',
        'code',
        'label',
        'max_uses',
        'uses_count',
        'expires_at',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'uses_count' => 0,
        'is_active' => true,
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'invitation_id');
    }

    public function incrementUses(): void
    {
        $this->increment('uses_count');
    }

    public function remainingUses(): ?int
    {
        return $this->max_uses ? max(0, $this->max_uses - $this->uses_count) : null;
    }

    public function hasExpired(): bool
    {
        return $this->expires_at !== null && now()->greaterThan($this->expires_at);
    }

    public function getIsValidAttribute(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->hasExpired()) {
            return false;
        }

        $remaining = $this->remainingUses();

        return is_null($remaining) || $remaining > 0;
    }
}

