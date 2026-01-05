<?php

namespace App\Services\Mcp\Resources;

use App\Exceptions\Mcp\ResourceNotFoundException;
use App\Models\TimeEntry;
use App\Services\Mcp\ErrorHandlingService;
use App\Services\Mcp\PaginationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TimeEntryResource
{
    public function __construct(
        private PaginationService $pagination,
        private ErrorHandlingService $errorHandler
    ) {}

    /**
     * List time entries with optional filters and pagination
     *
     * @param  array  $filters  Optional filters: task_id, user_id, running, page, per_page
     */
    public function list(array $filters = []): array
    {
        $query = TimeEntry::with(['task', 'user']);

        // Filter by task_id
        if (isset($filters['task_id'])) {
            $query->where('task_id', $filters['task_id']);
        }

        // Filter by user_id
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Filter running timers
        if (isset($filters['running']) && $filters['running']) {
            $query->running();
        }

        $query->orderBy('started_at', 'desc');

        // Use pagination service
        $result = $this->pagination->paginate($query, $filters);

        return [
            'time_entries' => collect($result['data'])->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'task_id' => $entry->task_id,
                    'task_title' => $entry->task?->title,
                    'user_id' => $entry->user_id,
                    'user_name' => $entry->user?->name,
                    'started_at' => $entry->started_at,
                    'stopped_at' => $entry->stopped_at,
                    'duration_seconds' => $entry->duration_seconds,
                    'duration_hours' => $entry->getDurationInHours(),
                    'formatted_duration' => $entry->formatted_duration,
                    'is_running' => $entry->is_running,
                    'notes' => $entry->notes,
                    'created_at' => $entry->created_at,
                    'updated_at' => $entry->updated_at,
                ];
            })->toArray(),
            'pagination' => $result['pagination'],
        ];
    }

    /**
     * Get a single time entry
     *
     * @throws ResourceNotFoundException
     */
    public function get(int $id): array
    {
        try {
            $entry = TimeEntry::with(['task', 'user'])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('time_entry', $id);
        }

        return [
            'id' => $entry->id,
            'task_id' => $entry->task_id,
            'task' => $entry->task ? [
                'id' => $entry->task->id,
                'title' => $entry->task->title,
                'status' => $entry->task->status->value,
                'project_id' => $entry->task->project_id,
                'project_name' => $entry->task->project?->name,
            ] : null,
            'user_id' => $entry->user_id,
            'user' => $entry->user ? [
                'id' => $entry->user->id,
                'name' => $entry->user->name,
                'email' => $entry->user->email,
            ] : null,
            'started_at' => $entry->started_at,
            'stopped_at' => $entry->stopped_at,
            'duration_seconds' => $entry->duration_seconds,
            'duration_hours' => $entry->getDurationInHours(),
            'formatted_duration' => $entry->formatted_duration,
            'is_running' => $entry->is_running,
            'notes' => $entry->notes,
            'created_at' => $entry->created_at,
            'updated_at' => $entry->updated_at,
        ];
    }
}
