<?php

namespace App\Services\Mcp\Tools;

use App\Http\Requests\Mcp\Project\CreateProjectRequest;
use App\Http\Requests\Mcp\Project\UpdateProjectRequest;
use App\Models\Project;
use App\Services\Mcp\AuditLogService;
use Illuminate\Support\Facades\Validator;

class ProjectTools
{
    public function __construct(
        private AuditLogService $auditLog
    ) {}

    /**
     * Create a new project
     *
     * @param array $args Project data
     * @return array
     */
    public function create(array $args): array
    {
        // Validate input
        $request = new CreateProjectRequest();
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

            $project = Project::create([
                'client_id' => $validated['client_id'] ?? null,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? 'draft',
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'color' => $validated['color'] ?? null,
            ]);

            $this->auditLog->log(
                'create',
                'project',
                $project->id,
                $project->toArray()
            );

            return [
                'success' => true,
                'message' => 'Project created successfully',
                'id' => $project->id,
                'name' => $project->name,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create project',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update an existing project
     *
     * @param array $args Project data with id
     * @return array
     */
    public function update(array $args): array
    {
        // Validate input
        $request = new UpdateProjectRequest();
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
            $project = Project::findOrFail($validated['id']);
            $oldValues = $project->toArray();

            $fillable = [
                'client_id', 'name', 'description', 'status',
                'start_date', 'end_date', 'color'
            ];

            $updates = array_intersect_key($validated, array_flip($fillable));
            $project->update($updates);

            $this->auditLog->log(
                'update',
                'project',
                $project->id,
                [
                    'old' => array_intersect_key($oldValues, $updates),
                    'new' => $updates,
                ]
            );

            return [
                'success' => true,
                'message' => 'Project updated successfully',
                'id' => $project->id,
                'name' => $project->name,
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Project not found',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update project',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a project (soft delete)
     *
     * @param int $id Project ID
     * @return array
     */
    public function delete(int $id): array
    {
        try {
            $project = Project::findOrFail($id);

            // Check if project has related records
            if ($project->tasks()->count() > 0 ||
                $project->quotes()->count() > 0 ||
                $project->invoices()->count() > 0) {
                return [
                    'success' => false,
                    'message' => 'Cannot delete project with related records (tasks, quotes, or invoices)',
                ];
            }

            $projectName = $project->name;
            $project->delete();

            $this->auditLog->log(
                'delete',
                'project',
                $id,
                [
                    'name' => $projectName,
                    'client_id' => $project->client_id,
                ]
            );

            return [
                'success' => true,
                'message' => 'Project deleted successfully',
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Project not found',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete project',
                'error' => $e->getMessage(),
            ];
        }
    }
}
