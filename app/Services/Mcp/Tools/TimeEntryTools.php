<?php

namespace App\Services\Mcp\Tools;

use App\Http\Requests\Mcp\TimeEntry\CreateTimeEntryRequest;
use App\Models\TimeEntry;
use App\Services\Mcp\AuditLogService;
use Illuminate\Support\Facades\Validator;

class TimeEntryTools
{
    public function __construct(
        private AuditLogService $auditLog
    ) {}

    /**
     * Create a new time entry (start timer or manual entry)
     *
     * @param array $args Time entry data
     * @return array
     */
    public function create(array $args): array
    {
        // Validate input
        $request = new CreateTimeEntryRequest();
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

            // If stopped_at is provided, it's a manual entry
            $isManual = isset($validated['stopped_at']);

            $timeEntry = TimeEntry::create([
                'task_id' => $validated['task_id'],
                'user_id' => $validated['user_id'] ?? auth()->id() ?? 1,
                'started_at' => $validated['started_at'] ?? now(),
                'stopped_at' => $validated['stopped_at'] ?? null,
                'duration_seconds' => $validated['duration_seconds'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $this->auditLog->log(
                'create',
                'time_entry',
                $timeEntry->id,
                $timeEntry->toArray()
            );

            return [
                'success' => true,
                'message' => $isManual ? 'Time entry created successfully' : 'Timer started successfully',
                'id' => $timeEntry->id,
                'is_running' => $timeEntry->is_running,
                'duration_hours' => $timeEntry->getDurationInHours(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create time entry',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Stop a running time entry
     *
     * @param int $id Time entry ID
     * @param string|null $notes Optional notes
     * @return array
     */
    public function stop(int $id, ?string $notes = null): array
    {
        try {
            $timeEntry = TimeEntry::findOrFail($id);

            if (!$timeEntry->is_running) {
                return [
                    'success' => false,
                    'message' => 'Timer is already stopped',
                ];
            }

            $oldValues = $timeEntry->toArray();
            $timeEntry->stop($notes);

            $this->auditLog->log(
                'stop',
                'time_entry',
                $timeEntry->id,
                [
                    'old' => $oldValues,
                    'new' => [
                        'stopped_at' => $timeEntry->stopped_at,
                        'duration_seconds' => $timeEntry->duration_seconds,
                        'notes' => $timeEntry->notes,
                    ],
                ]
            );

            return [
                'success' => true,
                'message' => 'Timer stopped successfully',
                'id' => $timeEntry->id,
                'duration_seconds' => $timeEntry->duration_seconds,
                'duration_hours' => $timeEntry->getDurationInHours(),
                'formatted_duration' => $timeEntry->formatted_duration,
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Time entry not found',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to stop timer',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a time entry (soft delete)
     *
     * @param int $id Time entry ID
     * @return array
     */
    public function delete(int $id): array
    {
        try {
            $timeEntry = TimeEntry::findOrFail($id);

            // Don't allow deleting running timers
            if ($timeEntry->is_running) {
                return [
                    'success' => false,
                    'message' => 'Cannot delete a running timer. Stop it first.',
                ];
            }

            $timeEntry->delete();

            $this->auditLog->log(
                'delete',
                'time_entry',
                $id,
                [
                    'task_id' => $timeEntry->task_id,
                    'duration_seconds' => $timeEntry->duration_seconds,
                ]
            );

            return [
                'success' => true,
                'message' => 'Time entry deleted successfully',
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Time entry not found',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete time entry',
                'error' => $e->getMessage(),
            ];
        }
    }
}
