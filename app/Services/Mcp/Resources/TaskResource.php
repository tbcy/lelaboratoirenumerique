<?php

namespace App\Services\Mcp\Resources;

use App\Exceptions\Mcp\ResourceNotFoundException;
use App\Models\Task;
use App\Services\Mcp\ErrorHandlingService;
use App\Services\Mcp\PaginationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TaskResource
{
    public function __construct(
        private PaginationService $pagination,
        private ErrorHandlingService $errorHandler
    ) {}

    /**
     * List tasks with optional filters and pagination
     *
     * @param  array  $filters  Optional filters: status, priority, project_id, client_id, overdue, page, per_page
     */
    public function list(array $filters = []): array
    {
        $query = Task::with(['project', 'client', 'catalogItem', 'parent']);

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by priority
        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        // Filter by project_id
        if (isset($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        // Filter by client_id
        if (isset($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        // Filter overdue tasks
        if (isset($filters['overdue']) && $filters['overdue']) {
            $query->whereNotIn('status', ['done'])
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', now());
        }

        $query->orderBy('sort_order')->orderBy('created_at', 'desc');

        // Use pagination service
        $result = $this->pagination->paginate($query, $filters);

        return [
            'tasks' => collect($result['data'])->map(function ($task) {
                return [
                    'id' => $task->id,
                    'project_id' => $task->project_id,
                    'project_name' => $task->project?->name,
                    'client_id' => $task->client_id,
                    'client_name' => $task->client?->display_name,
                    'catalog_item_id' => $task->catalog_item_id,
                    'catalog_item_name' => $task->catalogItem?->name,
                    'parent_id' => $task->parent_id,
                    'parent_title' => $task->parent?->title,
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => $task->status->value,
                    'priority' => $task->priority,
                    'due_date' => $task->due_date,
                    'estimated_minutes' => $task->estimated_minutes,
                    'estimated_hours' => $task->estimated_hours,
                    'logged_hours' => $task->logged_hours,
                    'is_overdue' => $task->is_overdue,
                    'sort_order' => $task->sort_order,
                    'created_at' => $task->created_at,
                    'updated_at' => $task->updated_at,
                ];
            })->toArray(),
            'pagination' => $result['pagination'],
        ];
    }

    /**
     * Get a single task with related data
     *
     * @throws ResourceNotFoundException
     */
    public function get(int $id): array
    {
        try {
            $task = Task::with([
                'project',
                'client',
                'catalogItem',
                'parent',
                'subtasks',
                'timeEntries' => function ($query) {
                    $query->orderBy('started_at', 'desc');
                },
            ])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('task', $id);
        }

        return [
            'id' => $task->id,
            'project_id' => $task->project_id,
            'project' => $task->project ? [
                'id' => $task->project->id,
                'name' => $task->project->name,
            ] : null,
            'client_id' => $task->client_id,
            'client' => $task->client ? [
                'id' => $task->client->id,
                'display_name' => $task->client->display_name,
            ] : null,
            'catalog_item_id' => $task->catalog_item_id,
            'catalog_item' => $task->catalogItem ? [
                'id' => $task->catalogItem->id,
                'name' => $task->catalogItem->name,
            ] : null,
            'parent_id' => $task->parent_id,
            'parent' => $task->parent ? [
                'id' => $task->parent->id,
                'title' => $task->parent->title,
            ] : null,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status->value,
            'priority' => $task->priority,
            'due_date' => $task->due_date,
            'estimated_minutes' => $task->estimated_minutes,
            'estimated_hours' => $task->estimated_hours,
            'logged_hours' => $task->logged_hours,
            'is_overdue' => $task->is_overdue,
            'sort_order' => $task->sort_order,
            'created_at' => $task->created_at,
            'updated_at' => $task->updated_at,
            'subtasks' => $task->subtasks->map(fn ($subtask) => [
                'id' => $subtask->id,
                'title' => $subtask->title,
                'status' => $subtask->status->value,
                'priority' => $subtask->priority,
                'due_date' => $subtask->due_date,
            ])->toArray(),
            'time_entries' => $task->timeEntries->map(fn ($entry) => [
                'id' => $entry->id,
                'user_id' => $entry->user_id,
                'started_at' => $entry->started_at,
                'stopped_at' => $entry->stopped_at,
                'duration_seconds' => $entry->duration_seconds,
                'duration_hours' => $entry->duration_hours,
                'notes' => $entry->notes,
                'is_running' => $entry->is_running,
            ])->toArray(),
        ];
    }
}
