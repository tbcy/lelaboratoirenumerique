<?php

namespace App\Services\Mcp\Tools;

use App\Http\Requests\Mcp\Task\CreateTaskRequest;
use App\Http\Requests\Mcp\Task\UpdateTaskRequest;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Services\Mcp\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TaskTools
{
    public function __construct(
        private AuditLogService $auditLog
    ) {}

    /**
     * Create a new task
     *
     * @param array $args Task data
     * @return array
     */
    public function create(array $args): array
    {
        // Validate input
        $request = new CreateTaskRequest();
        $validator = Validator::make($args, $request->rules(), $request->messages());

        if (method_exists($request, 'withValidator')) {
            $request->withValidator($validator);
        }

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ];
        }

        try {
            $validated = $validator->validated();

            $task = Task::create([
                'project_id' => $validated['project_id'] ?? null,
                'client_id' => $validated['client_id'] ?? null,
                'catalog_item_id' => $validated['catalog_item_id'] ?? null,
                'parent_id' => $validated['parent_id'] ?? null,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? 'todo',
                'priority' => $validated['priority'] ?? 'medium',
                'due_date' => $validated['due_date'] ?? null,
                'estimated_minutes' => $validated['estimated_minutes'] ?? null,
                'sort_order' => $validated['sort_order'] ?? null,
            ]);

            $this->auditLog->log(
                'create',
                'task',
                $task->id,
                $task->toArray()
            );

            return [
                'success' => true,
                'message' => 'Task created successfully',
                'id' => $task->id,
                'title' => $task->title,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create task',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update task status
     *
     * @param int $id Task ID
     * @param string $status New status
     * @return array
     */
    public function updateStatus(int $id, string $status): array
    {
        try {
            $task = Task::findOrFail($id);
            $oldStatus = $task->status->value;

            $task->update(['status' => $status]);

            $this->auditLog->log(
                'update_status',
                'task',
                $task->id,
                [
                    'old' => ['status' => $oldStatus],
                    'new' => ['status' => $status],
                ]
            );

            return [
                'success' => true,
                'message' => "Task status updated to {$status}",
                'id' => $task->id,
                'status' => $task->status->value,
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Task not found',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update task status',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Log time to a task (manual time entry)
     *
     * @param int $id Task ID
     * @param array $args Time entry data (started_at, stopped_at, duration_seconds, notes, user_id)
     * @return array
     */
    public function logTime(int $id, array $args): array
    {
        try {
            $task = Task::findOrFail($id);

            // Create manual time entry
            $timeEntry = $task->timeEntries()->create([
                'user_id' => $args['user_id'] ?? auth()->id() ?? 1, // Default to user 1 for API
                'started_at' => $args['started_at'] ?? now(),
                'stopped_at' => $args['stopped_at'] ?? now(),
                'duration_seconds' => $args['duration_seconds'] ?? null,
                'notes' => $args['notes'] ?? null,
            ]);

            $this->auditLog->log(
                'log_time',
                'task',
                $task->id,
                [
                    'time_entry_id' => $timeEntry->id,
                    'duration_seconds' => $timeEntry->duration_seconds,
                    'duration_hours' => $timeEntry->duration_hours,
                ]
            );

            return [
                'success' => true,
                'message' => 'Time logged successfully',
                'time_entry_id' => $timeEntry->id,
                'duration_hours' => $timeEntry->duration_hours,
                'total_logged_hours' => $task->fresh()->logged_hours,
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Task not found',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to log time',
                'error' => $e->getMessage(),
            ];
        }
    }
}
