<?php

namespace App\Services\Mcp\Resources;

use App\Exceptions\Mcp\ResourceNotFoundException;
use App\Models\Project;
use App\Services\Mcp\ErrorHandlingService;
use App\Services\Mcp\PaginationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProjectResource
{
    public function __construct(
        private PaginationService $pagination,
        private ErrorHandlingService $errorHandler
    ) {}

    /**
     * List projects with optional filters and pagination
     *
     * @param  array  $filters  Optional filters: status, client_id, page, per_page
     */
    public function list(array $filters = []): array
    {
        $query = Project::with(['client']);

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by client_id
        if (isset($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        $query->orderBy('created_at', 'desc');

        // Use pagination service
        $result = $this->pagination->paginate($query, $filters);

        return [
            'projects' => collect($result['data'])->map(function ($project) {
                return [
                    'id' => $project->id,
                    'client_id' => $project->client_id,
                    'client_name' => $project->client?->display_name,
                    'name' => $project->name,
                    'description' => $project->description,
                    'status' => $project->status,
                    'start_date' => $project->start_date,
                    'end_date' => $project->end_date,
                    'budget' => (float) $project->budget,
                    'estimated_hours' => $project->estimated_hours,
                    'total_logged_hours' => $project->total_logged_hours,
                    'color' => $project->color,
                    'created_at' => $project->created_at,
                    'updated_at' => $project->updated_at,
                ];
            })->toArray(),
            'pagination' => $result['pagination'],
        ];
    }

    /**
     * Get a single project with related data
     *
     * @throws ResourceNotFoundException
     */
    public function get(int $id): array
    {
        try {
            $project = Project::with([
                'client',
                'tasks' => function ($query) {
                    $query->orderBy('sort_order')->orderBy('created_at', 'desc');
                },
                'quotes' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'invoices' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'timeEntries' => function ($query) {
                    $query->orderBy('started_at', 'desc')->limit(10);
                },
            ])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('project', $id);
        }

        return [
            'id' => $project->id,
            'client_id' => $project->client_id,
            'client' => $project->client ? [
                'id' => $project->client->id,
                'display_name' => $project->client->display_name,
                'email' => $project->client->email,
            ] : null,
            'name' => $project->name,
            'description' => $project->description,
            'status' => $project->status,
            'start_date' => $project->start_date,
            'end_date' => $project->end_date,
            'budget' => (float) $project->budget,
            'estimated_hours' => $project->estimated_hours,
            'total_logged_hours' => $project->total_logged_hours,
            'color' => $project->color,
            'created_at' => $project->created_at,
            'updated_at' => $project->updated_at,
            'tasks' => $project->tasks->map(fn ($task) => [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status->value,
                'priority' => $task->priority,
                'due_date' => $task->due_date,
            ])->toArray(),
            'quotes' => $project->quotes->map(fn ($quote) => [
                'id' => $quote->id,
                'number' => $quote->number,
                'status' => $quote->status,
                'total_ttc' => (float) $quote->total_ttc,
            ])->toArray(),
            'invoices' => $project->invoices->map(fn ($invoice) => [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'status' => $invoice->status,
                'total_ttc' => (float) $invoice->total_ttc,
            ])->toArray(),
            'time_entries' => $project->timeEntries->map(fn ($entry) => [
                'id' => $entry->id,
                'task_id' => $entry->task_id,
                'task_title' => $entry->task?->title,
                'user_id' => $entry->user_id,
                'started_at' => $entry->started_at,
                'duration_hours' => $entry->duration_hours,
            ])->toArray(),
            'stats' => [
                'tasks_count' => $project->tasks->count(),
                'quotes_count' => $project->quotes->count(),
                'invoices_count' => $project->invoices->count(),
                'progress_percent' => $project->estimated_hours > 0
                    ? min(100, round(($project->total_logged_hours / $project->estimated_hours) * 100))
                    : null,
            ],
        ];
    }
}
