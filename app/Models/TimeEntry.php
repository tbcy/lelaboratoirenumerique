<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class TimeEntry extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'task_id',
        'user_id',
        'started_at',
        'stopped_at',
        'duration_seconds',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'stopped_at' => 'datetime',
        'duration_seconds' => 'integer',
    ];

    /**
     * Relationships
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopeRunning(Builder $query): Builder
    {
        return $query->whereNull('stopped_at');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeInPeriod(Builder $query, Carbon $startDate, Carbon $endDate): Builder
    {
        return $query->whereBetween('started_at', [$startDate, $endDate]);
    }

    /**
     * Methods
     */
    public function stop(?string $notes = null): void
    {
        if ($this->stopped_at) {
            throw new \Exception('Timer already stopped');
        }

        $this->update([
            'stopped_at' => now(),
            'duration_seconds' => $this->started_at->diffInSeconds(now()),
            'notes' => $notes,
        ]);
    }

    public function getElapsedSeconds(): int
    {
        if ($this->stopped_at) {
            return $this->duration_seconds;
        }

        return $this->started_at->diffInSeconds(now());
    }

    public function getDurationInHours(): float
    {
        if (!$this->duration_seconds) {
            return 0;
        }

        return round($this->duration_seconds / 3600, 2);
    }

    public function getIsRunningAttribute(): bool
    {
        return is_null($this->stopped_at);
    }

    public function getFormattedDurationAttribute(): string
    {
        $seconds = $this->getElapsedSeconds();
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
        }

        return sprintf('%02d:%02d', $minutes, $secs);
    }
}
