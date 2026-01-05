<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Mcp\Resources\ClientResource;
use App\Services\Mcp\Tools\ClientTools;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct(
        private ClientResource $resource,
        private ClientTools $tools
    ) {}

    /**
     * List all clients
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'type', 'search', 'page', 'per_page']);
        $result = $this->resource->list($filters);
        return response()->json($result);
    }

    /**
     * Get a specific client
     */
    public function show(int $id): JsonResponse
    {
        try {
            $result = $this->resource->get($id);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Create a new client
     */
    public function store(Request $request): JsonResponse
    {
        $result = $this->tools->create($request->all());
        $statusCode = $result['success'] ? 201 : 400;
        return response()->json($result, $statusCode);
    }

    /**
     * Update a client
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $data = array_merge($request->all(), ['id' => $id]);
        $result = $this->tools->update($data);
        $statusCode = $result['success'] ? 200 : 400;
        return response()->json($result, $statusCode);
    }

    /**
     * Delete a client
     */
    public function destroy(int $id): JsonResponse
    {
        $result = $this->tools->delete($id);
        $statusCode = $result['success'] ? 200 : 400;
        return response()->json($result, $statusCode);
    }
}
