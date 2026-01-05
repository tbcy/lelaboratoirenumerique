<?php

namespace App\Services\Mcp\Resources;

use App\Exceptions\Mcp\ResourceNotFoundException;
use App\Models\Client;
use App\Services\Mcp\ErrorHandlingService;
use App\Services\Mcp\PaginationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ClientResource
{
    public function __construct(
        private PaginationService $pagination,
        private ErrorHandlingService $errorHandler
    ) {}

    /**
     * List available resource URIs for MCP
     */
    public function listResources(): array
    {
        return [
            [
                'uri' => 'helper://clients',
                'name' => 'Clients List',
                'description' => 'List all clients with optional filters',
                'mimeType' => 'application/json',
            ],
        ];
    }

    /**
     * Read resource by path for MCP
     *
     * @param  string  $path  Path portion of URI (e.g., "list" or numeric ID)
     * @param  array  $params  Additional parameters
     */
    public function read(string $path, array $params = []): array
    {
        // If path is empty or "list", return list
        if (empty($path) || $path === 'list') {
            return $this->list($params);
        }

        // If path is numeric, treat as ID
        if (is_numeric($path)) {
            $id = $this->errorHandler->validateId($path, 'client');

            return $this->get($id);
        }

        throw new \InvalidArgumentException("Invalid path for clients resource: {$path}");
    }

    /**
     * List clients with optional filters and pagination
     *
     * @param  array  $filters  Optional filters: status, type, search, page, per_page
     */
    public function list(array $filters = []): array
    {
        $query = Client::query();

        // Filter by status (prospect, client, inactive)
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by type (company, individual)
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Search in company_name, first_name, last_name, email
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $query->orderBy('created_at', 'desc');

        // Use pagination service
        $result = $this->pagination->paginate($query, $filters);

        return [
            'clients' => collect($result['data'])->map(function ($client) {
                return [
                    'id' => $client->id,
                    'type' => $client->type,
                    'display_name' => $client->display_name,
                    'company_name' => $client->company_name,
                    'first_name' => $client->first_name,
                    'last_name' => $client->last_name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'mobile' => $client->mobile,
                    'status' => $client->status,
                    'city' => $client->city,
                    'country' => $client->country,
                    'created_at' => $client->created_at,
                    'updated_at' => $client->updated_at,
                ];
            })->toArray(),
            'pagination' => $result['pagination'],
        ];
    }

    /**
     * Get a single client with related data
     *
     * @throws ResourceNotFoundException
     */
    public function get(int $id): array
    {
        try {
            $client = Client::with([
                'quotes' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'invoices' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'projects' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'tasks' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
            ])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('client', $id);
        }

        return [
            'id' => $client->id,
            'type' => $client->type,
            'display_name' => $client->display_name,
            'company_name' => $client->company_name,
            'first_name' => $client->first_name,
            'last_name' => $client->last_name,
            'email' => $client->email,
            'phone' => $client->phone,
            'mobile' => $client->mobile,
            'address' => $client->address,
            'address_2' => $client->address_2,
            'postal_code' => $client->postal_code,
            'city' => $client->city,
            'country' => $client->country,
            'full_address' => $client->full_address,
            'siret' => $client->siret,
            'vat_number' => $client->vat_number,
            'status' => $client->status,
            'notes' => $client->notes,
            'tags' => $client->tags,
            'created_at' => $client->created_at,
            'updated_at' => $client->updated_at,
            'quotes' => $client->quotes->map(fn ($q) => [
                'id' => $q->id,
                'number' => $q->number,
                'subject' => $q->subject,
                'status' => $q->status,
                'total_ttc' => $q->total_ttc,
                'created_at' => $q->created_at,
            ])->toArray(),
            'invoices' => $client->invoices->map(fn ($i) => [
                'id' => $i->id,
                'number' => $i->number,
                'subject' => $i->subject,
                'status' => $i->status,
                'total_ttc' => $i->total_ttc,
                'created_at' => $i->created_at,
            ])->toArray(),
            'projects' => $client->projects->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'status' => $p->status,
                'created_at' => $p->created_at,
            ])->toArray(),
            'tasks' => $client->tasks->map(fn ($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'status' => $t->status,
                'created_at' => $t->created_at,
            ])->toArray(),
            'stats' => [
                'quotes_count' => $client->quotes->count(),
                'invoices_count' => $client->invoices->count(),
                'projects_count' => $client->projects->count(),
                'tasks_count' => $client->tasks->count(),
            ],
        ];
    }
}
