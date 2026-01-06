<?php

namespace App\Services\Mcp\Tools;

use App\Exceptions\Mcp\InvalidOperationException;
use App\Exceptions\Mcp\ResourceNotFoundException;
use App\Models\Note;
use App\Services\Mcp\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;

class NoteTools
{
    public function __construct(
        private AuditLogService $auditLog
    ) {}

    public function create(array $args): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'datetime' => 'nullable|date',
            'short_summary' => 'nullable|string',
            'long_summary' => 'nullable|string',
            'notes' => 'nullable|string',
            'transcription' => 'nullable|string',
            'parent_id' => 'nullable|integer|exists:notes,id',
            'stakeholder_ids' => 'nullable|array',
            'stakeholder_ids.*' => 'integer|exists:stakeholders,id',
            'scope_ids' => 'nullable|array',
            'scope_ids.*' => 'integer|exists:note_scopes,id',
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

        // Extract relation ids before creating
        $stakeholderIds = $validated['stakeholder_ids'] ?? [];
        $scopeIds = $validated['scope_ids'] ?? [];
        unset($validated['stakeholder_ids'], $validated['scope_ids']);

        // Set default datetime if not provided
        if (empty($validated['datetime'])) {
            $validated['datetime'] = now();
        }

        $note = Note::create($validated);

        // Sync relations
        if (! empty($stakeholderIds)) {
            $note->stakeholders()->sync($stakeholderIds);
        }
        if (! empty($scopeIds)) {
            $note->scopes()->sync($scopeIds);
        }

        $this->auditLog->logCreate('note', $note->id, $note->load(['stakeholders', 'scopes'])->toArray());

        return [
            'success' => true,
            'message' => 'Note created successfully',
            'id' => $note->id,
            'name' => $note->name,
            'datetime' => $note->datetime?->toIso8601String(),
            'parent_id' => $note->parent_id,
            'stakeholder_ids' => $stakeholderIds,
            'scope_ids' => $scopeIds,
        ];
    }

    public function update(array $args): array
    {
        $id = $args['id'] ?? null;

        if (! $id) {
            return [
                'success' => false,
                'message' => 'Note ID is required',
            ];
        }

        try {
            $note = Note::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('note', $id);
        }

        $rules = [
            'id' => 'required|integer',
            'name' => 'nullable|string|max:255',
            'datetime' => 'nullable|date',
            'short_summary' => 'nullable|string',
            'long_summary' => 'nullable|string',
            'notes' => 'nullable|string',
            'transcription' => 'nullable|string',
            'parent_id' => 'nullable|integer|exists:notes,id',
            'stakeholder_ids' => 'nullable|array',
            'stakeholder_ids.*' => 'integer|exists:stakeholders,id',
            'scope_ids' => 'nullable|array',
            'scope_ids.*' => 'integer|exists:note_scopes,id',
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
        $oldData = $note->load(['stakeholders', 'scopes'])->toArray();

        // Extract relation ids
        $stakeholderIds = $validated['stakeholder_ids'] ?? null;
        $scopeIds = $validated['scope_ids'] ?? null;
        unset($validated['stakeholder_ids'], $validated['scope_ids'], $validated['id']);

        // Prevent circular parent relationship
        if (isset($validated['parent_id']) && $validated['parent_id'] == $id) {
            throw InvalidOperationException::make('A note cannot be its own parent', [
                'note_id' => $id,
            ]);
        }

        // Update note fields
        $note->update($validated);

        // Sync relations if provided
        if ($stakeholderIds !== null) {
            $note->stakeholders()->sync($stakeholderIds);
        }
        if ($scopeIds !== null) {
            $note->scopes()->sync($scopeIds);
        }

        $this->auditLog->logUpdate('note', $note->id, $note->fresh()->load(['stakeholders', 'scopes'])->toArray(), $oldData);

        return [
            'success' => true,
            'message' => 'Note updated successfully',
            'id' => $note->id,
            'name' => $note->name,
            'datetime' => $note->datetime?->toIso8601String(),
            'parent_id' => $note->parent_id,
        ];
    }

    public function delete(array $args): array
    {
        $id = $args['id'] ?? null;

        if (! $id) {
            return [
                'success' => false,
                'message' => 'Note ID is required',
            ];
        }

        try {
            $note = Note::withCount('children')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('note', $id);
        }

        $noteData = $note->load(['stakeholders', 'scopes'])->toArray();

        // Soft delete (children remain but become orphans)
        $note->delete();

        $this->auditLog->logDelete('note', $id, $noteData);

        return [
            'success' => true,
            'message' => 'Note deleted successfully (soft delete)',
            'id' => $id,
            'had_children' => $note->children_count > 0,
        ];
    }

    /**
     * Change the parent of a note (move in hierarchy)
     */
    public function setParent(array $args): array
    {
        $id = $args['id'] ?? null;
        $parentId = $args['parent_id'] ?? null;

        if (! $id) {
            return [
                'success' => false,
                'message' => 'Note ID is required',
            ];
        }

        try {
            $note = Note::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('note', $id);
        }

        // Validate parent exists if provided
        if ($parentId !== null) {
            try {
                $parent = Note::findOrFail($parentId);
            } catch (ModelNotFoundException $e) {
                throw ResourceNotFoundException::make('note (parent)', $parentId);
            }

            // Prevent circular reference
            if ($parentId == $id) {
                throw InvalidOperationException::make('A note cannot be its own parent', [
                    'note_id' => $id,
                ]);
            }

            // Prevent setting a descendant as parent
            if ($this->isDescendant($note, $parentId)) {
                throw InvalidOperationException::make('Cannot set a descendant as parent (would create circular reference)', [
                    'note_id' => $id,
                    'parent_id' => $parentId,
                ]);
            }
        }

        $oldParentId = $note->parent_id;
        $oldData = $note->toArray();

        $note->update(['parent_id' => $parentId]);

        $this->auditLog->logUpdate('note', $note->id, $note->fresh()->toArray(), $oldData);

        return [
            'success' => true,
            'message' => $parentId ? 'Note moved to new parent' : 'Note moved to root level',
            'id' => $note->id,
            'name' => $note->name,
            'old_parent_id' => $oldParentId,
            'new_parent_id' => $parentId,
        ];
    }

    /**
     * Check if a note is a descendant of another note
     */
    private function isDescendant(Note $note, int $potentialParentId): bool
    {
        $children = $note->children()->pluck('id')->toArray();

        if (in_array($potentialParentId, $children)) {
            return true;
        }

        foreach ($note->children as $child) {
            if ($this->isDescendant($child, $potentialParentId)) {
                return true;
            }
        }

        return false;
    }
}
