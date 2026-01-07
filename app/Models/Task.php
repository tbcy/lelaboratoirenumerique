<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Task extends Model implements Sortable
{
    use SoftDeletes, SortableTrait;

    public $sortable = [
        'order_column_name' => 'sort_order',
        'sort_when_creating' => true,
    ];

    protected $fillable = [
        'project_id',
        'client_id',
        'catalog_item_id',
        'parent_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'estimated_minutes',
        'sort_order',
    ];

    protected $casts = [
        'status' => TaskStatus::class,
        'due_date' => 'date',
        'estimated_minutes' => 'integer',
        'sort_order' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(CatalogItem::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id');
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class)->orderBy('started_at', 'desc');
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'loggable');
    }

    public function stakeholders(): BelongsToMany
    {
        return $this->belongsToMany(Stakeholder::class)->withTimestamps();
    }

    public static function getStatusOptions(): array
    {
        return [
            'todo' => __('enums.task_status.todo'),
            'in_progress' => __('enums.task_status.in_progress'),
            'review' => __('enums.task_status.review'),
            'done' => __('enums.task_status.done'),
        ];
    }

    public static function getPriorityOptions(): array
    {
        return [
            'low' => __('enums.task_priority.low'),
            'medium' => __('enums.task_priority.medium'),
            'high' => __('enums.task_priority.high'),
            'urgent' => __('enums.task_priority.urgent'),
        ];
    }

    public function getEstimatedHoursAttribute(): ?float
    {
        return $this->estimated_minutes ? $this->estimated_minutes / 60 : null;
    }

    public function getLoggedHoursAttribute(): float
    {
        return $this->getTotalLoggedHours();
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'done'
            && $this->due_date
            && $this->due_date->isPast();
    }

    /**
     * Timer Methods
     */
    public function startTimer(?int $userId = null): TimeEntry
    {
        $userId = $userId ?? auth()->id();

        // Stop active timer for user (only one at a time)
        $activeTimer = TimeEntry::forUser($userId)->running()->first();
        if ($activeTimer) {
            $activeTimer->stop();
        }

        return $this->timeEntries()->create([
            'user_id' => $userId,
            'started_at' => now(),
        ]);
    }

    public function stopTimer(?int $userId = null, ?string $notes = null): void
    {
        $activeTimer = $this->timeEntries()
            ->forUser($userId ?? auth()->id())
            ->running()
            ->first();

        if (!$activeTimer) {
            throw new \Exception('No active timer found');
        }

        $activeTimer->stop($notes);
    }

    public function getActiveTimer(?int $userId = null): ?TimeEntry
    {
        return $this->timeEntries()
            ->forUser($userId ?? auth()->id())
            ->running()
            ->first();
    }

    public function getTotalLoggedSeconds(): int
    {
        return $this->timeEntries()
            ->whereNotNull('stopped_at')
            ->sum('duration_seconds');
    }

    public function getTotalLoggedHours(): float
    {
        return round($this->getTotalLoggedSeconds() / 3600, 2);
    }
}
