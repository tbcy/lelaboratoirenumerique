<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\NoteScope;
use App\Models\Stakeholder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class NoteApiController extends Controller
{
    /**
     * Create a new note with optional stakeholders and scopes
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'datetime' => 'nullable|date',
            'short_summary' => 'nullable|string',
            'long_summary' => 'nullable|string',
            'notes' => 'nullable|string',
            'transcription' => 'nullable|string',
            'parent_id' => 'nullable|integer|exists:notes,id',

            // Stakeholders - can be IDs or new stakeholders to create
            'stakeholder_ids' => 'nullable|array',
            'stakeholder_ids.*' => 'integer|exists:stakeholders,id',
            'stakeholders' => 'nullable|array',
            'stakeholders.*.name' => 'required_with:stakeholders|string|max:255',
            'stakeholders.*.email' => 'nullable|email|max:255',
            'stakeholders.*.phone' => 'nullable|string|max:50',
            'stakeholders.*.company' => 'nullable|string|max:255',
            'stakeholders.*.role' => 'nullable|string|max:255',

            // Scopes - can be IDs or new scopes to create
            'scope_ids' => 'nullable|array',
            'scope_ids.*' => 'integer|exists:note_scopes,id',
            'scopes' => 'nullable|array',
            'scopes.*.name' => 'required_with:scopes|string|max:255',
            'scopes.*.slug' => 'nullable|string|max:255',
            'scopes.*.color' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create the note
            $noteData = $validator->validated();
            $noteData['datetime'] = $noteData['datetime'] ?? now();

            // Remove relation data from note creation
            unset(
                $noteData['stakeholder_ids'],
                $noteData['stakeholders'],
                $noteData['scope_ids'],
                $noteData['scopes']
            );

            $note = Note::create($noteData);

            // Handle stakeholders
            $stakeholderIds = $request->input('stakeholder_ids', []);

            // Create new stakeholders if provided
            if ($request->has('stakeholders')) {
                foreach ($request->input('stakeholders') as $stakeholderData) {
                    $stakeholder = Stakeholder::create([
                        'name' => $stakeholderData['name'],
                        'email' => $stakeholderData['email'] ?? null,
                        'phone' => $stakeholderData['phone'] ?? null,
                        'company' => $stakeholderData['company'] ?? null,
                        'role' => $stakeholderData['role'] ?? null,
                        'is_active' => true,
                    ]);
                    $stakeholderIds[] = $stakeholder->id;
                }
            }

            if (! empty($stakeholderIds)) {
                $note->stakeholders()->sync($stakeholderIds);
            }

            // Handle scopes
            $scopeIds = $request->input('scope_ids', []);

            // Create new scopes if provided
            if ($request->has('scopes')) {
                foreach ($request->input('scopes') as $scopeData) {
                    $slug = $scopeData['slug'] ?? Str::slug($scopeData['name']);

                    // Check if scope with this slug already exists
                    $existingScope = NoteScope::where('slug', $slug)->first();
                    if ($existingScope) {
                        $scopeIds[] = $existingScope->id;
                    } else {
                        $scope = NoteScope::create([
                            'name' => $scopeData['name'],
                            'slug' => $slug,
                            'color' => $scopeData['color'] ?? null,
                        ]);
                        $scopeIds[] = $scope->id;
                    }
                }
            }

            if (! empty($scopeIds)) {
                $note->scopes()->sync($scopeIds);
            }

            DB::commit();

            // Reload with relations
            $note->load(['stakeholders', 'scopes', 'parent']);

            Log::info('Note created via API', [
                'note_id' => $note->id,
                'name' => $note->name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Note created successfully',
                'data' => [
                    'id' => $note->id,
                    'name' => $note->name,
                    'datetime' => $note->datetime?->toIso8601String(),
                    'parent_id' => $note->parent_id,
                    'parent_name' => $note->parent?->name,
                    'short_summary' => $note->short_summary,
                    'long_summary' => $note->long_summary,
                    'notes' => $note->notes,
                    'transcription' => $note->transcription,
                    'stakeholders' => $note->stakeholders->map(fn ($s) => [
                        'id' => $s->id,
                        'name' => $s->name,
                        'email' => $s->email,
                        'company' => $s->company,
                    ])->toArray(),
                    'scopes' => $note->scopes->map(fn ($s) => [
                        'id' => $s->id,
                        'name' => $s->name,
                        'slug' => $s->slug,
                        'color' => $s->color,
                    ])->toArray(),
                    'created_at' => $note->created_at->toIso8601String(),
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Note API creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create note',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * List available stakeholders (for reference when creating notes)
     */
    public function listStakeholders(): JsonResponse
    {
        $stakeholders = Stakeholder::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'company', 'role']);

        return response()->json([
            'success' => true,
            'data' => $stakeholders,
        ]);
    }

    /**
     * List available scopes (for reference when creating notes)
     */
    public function listScopes(): JsonResponse
    {
        $scopes = NoteScope::orderBy('name')
            ->get(['id', 'name', 'slug', 'color']);

        return response()->json([
            'success' => true,
            'data' => $scopes,
        ]);
    }
}
