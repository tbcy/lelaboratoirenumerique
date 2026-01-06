<?php

namespace App\Services\Mcp\Tools;

use App\Exceptions\Mcp\InvalidOperationException;
use App\Exceptions\Mcp\ResourceNotFoundException;
use App\Models\NoteScope;
use App\Services\Mcp\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class NoteScopeTools
{
    public function __construct(
        private AuditLogService $auditLog
    ) {}

    public function create(array $args): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:note_scopes,slug',
            'color' => 'nullable|string|max:20',
        ];

        $validator = Validator::make($args, $rules);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ];
        }

        $validated = $validator->validated();

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $scope = NoteScope::create($validated);

        $this->auditLog->logCreate('note_scope', $scope->id, $scope->toArray());

        return [
            'success' => true,
            'message' => 'Note scope created successfully',
            'id' => $scope->id,
            'name' => $scope->name,
            'slug' => $scope->slug,
            'color' => $scope->color,
        ];
    }

    public function update(array $args): array
    {
        $id = $args['id'] ?? null;

        if (! $id) {
            return [
                'success' => false,
                'message' => 'Note Scope ID is required',
            ];
        }

        try {
            $scope = NoteScope::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('note_scope', $id);
        }

        $rules = [
            'id' => 'required|integer',
            'name' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:note_scopes,slug,'.$id,
            'color' => 'nullable|string|max:20',
        ];

        $validator = Validator::make($args, $rules);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ];
        }

        $validated = $validator->validated();
        $oldData = $scope->toArray();

        unset($validated['id']);

        // Update only provided fields
        $scope->update($validated);

        $this->auditLog->logUpdate('note_scope', $scope->id, $scope->fresh()->toArray(), $oldData);

        return [
            'success' => true,
            'message' => 'Note scope updated successfully',
            'id' => $scope->id,
            'name' => $scope->name,
            'slug' => $scope->slug,
            'color' => $scope->color,
        ];
    }

    public function delete(array $args): array
    {
        $id = $args['id'] ?? null;

        if (! $id) {
            return [
                'success' => false,
                'message' => 'Note Scope ID is required',
            ];
        }

        try {
            $scope = NoteScope::withCount('notes')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('note_scope', $id);
        }

        // Prevent deletion if scope is used by notes
        if ($scope->notes_count > 0) {
            throw InvalidOperationException::make(
                "Cannot delete scope '{$scope->name}' because it is used by {$scope->notes_count} note(s)",
                [
                    'scope_id' => $id,
                    'notes_count' => $scope->notes_count,
                ]
            );
        }

        $scopeData = $scope->toArray();
        $scope->delete();

        $this->auditLog->logDelete('note_scope', $id, $scopeData);

        return [
            'success' => true,
            'message' => 'Note scope deleted successfully',
            'id' => $id,
        ];
    }
}
