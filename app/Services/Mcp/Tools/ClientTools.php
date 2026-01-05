<?php

namespace App\Services\Mcp\Tools;

use App\Exceptions\Mcp\InvalidOperationException;
use App\Exceptions\Mcp\ResourceNotFoundException;
use App\Http\Requests\Mcp\Client\CreateClientRequest;
use App\Http\Requests\Mcp\Client\UpdateClientRequest;
use App\Models\Client;
use App\Services\Mcp\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ClientTools
{
    public function __construct(
        private AuditLogService $auditLog
    ) {}

    /**
     * Create a new client
     *
     * @param array $args Client data
     * @return array
     */
    public function create(array $args): array
    {
        // Validate input
        $request = new CreateClientRequest();
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

            $client = Client::create([
                'type' => $validated['type'] ?? 'company',
                'company_name' => $validated['company_name'] ?? null,
                'first_name' => $validated['first_name'] ?? null,
                'last_name' => $validated['last_name'] ?? null,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'mobile' => $validated['mobile'] ?? null,
                'address' => $validated['address'] ?? null,
                'address_2' => $validated['address_2'] ?? null,
                'postal_code' => $validated['postal_code'] ?? null,
                'city' => $validated['city'] ?? null,
                'country' => $validated['country'] ?? 'France',
                'siret' => $validated['siret'] ?? null,
                'vat_number' => $validated['vat_number'] ?? null,
                'status' => $validated['status'] ?? 'prospect',
                'notes' => $validated['notes'] ?? null,
                'tags' => $validated['tags'] ?? null,
            ]);

            $this->auditLog->log(
                'create',
                'client',
                $client->id,
                $client->toArray()
            );

            return [
                'success' => true,
                'message' => 'Client created successfully',
                'id' => $client->id,
                'display_name' => $client->display_name,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error_code' => 'CREATE_FAILED',
                'message' => 'Failed to create client',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update an existing client
     *
     * @param array $args Client data with id
     * @return array
     */
    public function update(array $args): array
    {
        // Validate input
        $request = new UpdateClientRequest();
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
            $client = Client::findOrFail($validated['id']);
            $oldValues = $client->toArray();

            $fillable = [
                'type', 'company_name', 'first_name', 'last_name',
                'email', 'phone', 'mobile', 'address', 'address_2',
                'postal_code', 'city', 'country', 'siret', 'vat_number',
                'status', 'notes', 'tags'
            ];

            $updates = array_intersect_key($validated, array_flip($fillable));
            $client->update($updates);

            $this->auditLog->log(
                'update',
                'client',
                $client->id,
                [
                    'old' => array_intersect_key($oldValues, $updates),
                    'new' => $updates,
                ]
            );

            return [
                'success' => true,
                'message' => 'Client updated successfully',
                'id' => $client->id,
                'display_name' => $client->display_name,
            ];
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('client', $validated['id']);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error_code' => 'UPDATE_FAILED',
                'message' => 'Failed to update client',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a client (soft delete)
     *
     * @param int $id Client ID
     * @return array
     * @throws ResourceNotFoundException
     * @throws InvalidOperationException
     */
    public function delete(int $id): array
    {
        try {
            $client = Client::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('client', $id);
        }

        // Check if client has related records
        $relatedCounts = [
            'quotes' => $client->quotes()->count(),
            'invoices' => $client->invoices()->count(),
            'projects' => $client->projects()->count(),
            'tasks' => $client->tasks()->count(),
        ];

        $totalRelated = array_sum($relatedCounts);

        if ($totalRelated > 0) {
            throw InvalidOperationException::make(
                'delete client',
                'Client has related records that must be removed first',
                [
                    'client_id' => $id,
                    'related_records' => array_filter($relatedCounts),
                ]
            );
        }

        $displayName = $client->display_name;
        $client->delete();

        $this->auditLog->log(
            'delete',
            'client',
            $id,
            [
                'display_name' => $displayName,
                'type' => $client->type,
            ]
        );

        return [
            'success' => true,
            'message' => 'Client deleted successfully',
        ];
    }
}
