<?php

namespace App\Services\Mcp\Tools;

use App\Exceptions\Mcp\ResourceNotFoundException;
use App\Models\Stakeholder;
use App\Services\Mcp\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;

class StakeholderTools
{
    public function __construct(
        private AuditLogService $auditLog
    ) {}

    public function create(array $args): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
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

        $stakeholder = Stakeholder::create([
            ...$validated,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $this->auditLog->logCreate('stakeholder', $stakeholder->id, $stakeholder->toArray());

        return [
            'success' => true,
            'message' => 'Stakeholder created successfully',
            'id' => $stakeholder->id,
            'name' => $stakeholder->name,
            'email' => $stakeholder->email,
            'company' => $stakeholder->company,
        ];
    }

    public function update(array $args): array
    {
        $id = $args['id'] ?? null;

        if (! $id) {
            return [
                'success' => false,
                'message' => 'Stakeholder ID is required',
            ];
        }

        try {
            $stakeholder = Stakeholder::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('stakeholder', $id);
        }

        $rules = [
            'id' => 'required|integer',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
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
        $oldData = $stakeholder->toArray();

        unset($validated['id']);

        // Update only provided fields
        $stakeholder->update($validated);

        $this->auditLog->logUpdate('stakeholder', $stakeholder->id, $stakeholder->fresh()->toArray(), $oldData);

        return [
            'success' => true,
            'message' => 'Stakeholder updated successfully',
            'id' => $stakeholder->id,
            'name' => $stakeholder->name,
            'email' => $stakeholder->email,
            'company' => $stakeholder->company,
        ];
    }

    public function delete(array $args): array
    {
        $id = $args['id'] ?? null;

        if (! $id) {
            return [
                'success' => false,
                'message' => 'Stakeholder ID is required',
            ];
        }

        try {
            $stakeholder = Stakeholder::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('stakeholder', $id);
        }

        $stakeholderData = $stakeholder->toArray();

        // Detach from all notes and tasks before deleting
        $stakeholder->notes()->detach();
        $stakeholder->tasks()->detach();
        $stakeholder->delete();

        $this->auditLog->logDelete('stakeholder', $id, $stakeholderData);

        return [
            'success' => true,
            'message' => 'Stakeholder deleted successfully',
            'id' => $id,
        ];
    }
}
