<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Collection;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'name',
        'description',
        'status',
        'start_date',
        'end_date',
        'color',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'estimated_hours' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function timeEntries(): HasManyThrough
    {
        return $this->hasManyThrough(TimeEntry::class, Task::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'loggable');
    }

    public static function getStatusOptions(): array
    {
        return [
            'draft' => __('enums.project_status.draft'),
            'active' => __('enums.project_status.active'),
            'on_hold' => __('enums.project_status.paused'),
            'completed' => __('enums.project_status.completed'),
            'cancelled' => __('enums.project_status.cancelled'),
        ];
    }

    public function getTotalLoggedHoursAttribute(): float
    {
        $totalSeconds = $this->timeEntries()
            ->whereNotNull('stopped_at')
            ->sum('duration_seconds');

        return round($totalSeconds / 3600, 2);
    }

    public function getTimeEntriesByTaskAttribute(): Collection
    {
        return $this->timeEntries()
            ->whereNotNull('stopped_at')
            ->with(['task', 'user'])
            ->get()
            ->groupBy('task_id')
            ->map(function ($entries) {
                return [
                    'task' => $entries->first()->task,
                    'total_seconds' => $entries->sum('duration_seconds'),
                    'total_hours' => round($entries->sum('duration_seconds') / 3600, 2),
                    'entries_count' => $entries->count(),
                    'last_activity' => $entries->max('stopped_at'),
                    'users' => $entries->pluck('user')->unique('id')->pluck('name'),
                ];
            });
    }

    /**
     * Calculate budget from accepted quotes
     */
    protected function getCalculatedBudgetAttribute(): float
    {
        return $this->quotes()
            ->where('status', 'accepted')
            ->sum('total_amount') ?? 0.0;
    }

    /**
     * Calculate estimated hours from accepted quotes
     * Only count quote lines with unit='hour'
     */
    protected function getCalculatedEstimatedHoursAttribute(): int
    {
        $quotesIds = $this->quotes()
            ->where('status', 'accepted')
            ->pluck('id');

        if ($quotesIds->isEmpty()) {
            return 0;
        }

        return QuoteLine::whereIn('quote_id', $quotesIds)
            ->where('unit', 'hour')
            ->sum('quantity') ?? 0;
    }

    /**
     * Get budget attribute - returns manual value if set, otherwise calculates from accepted quotes
     */
    protected function getBudgetAttribute($value): float
    {
        // Si une valeur manuelle existe, la conserver (projets existants)
        if ($value !== null) {
            return (float) $value;
        }

        // Sinon, calculer depuis les devis acceptés
        return $this->calculated_budget;
    }

    /**
     * Get estimated hours attribute - returns manual value if set, otherwise calculates from accepted quotes
     */
    protected function getEstimatedHoursAttribute($value): int
    {
        // Si une valeur manuelle existe, la conserver (projets existants)
        if ($value !== null) {
            return (int) $value;
        }

        // Sinon, calculer depuis les devis acceptés
        return $this->calculated_estimated_hours;
    }
}
