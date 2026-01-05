<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Mcp\Resources\QuoteResource;
use App\Services\Mcp\Tools\QuoteTools;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public function __construct(
        private QuoteResource $resource,
        private QuoteTools $tools
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'client_id', 'page', 'per_page']);
        $result = $this->resource->list($filters);
        return response()->json($result);
    }

    public function show(int $id): JsonResponse
    {
        try {
            $result = $this->resource->get($id);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $result = $this->tools->create($request->all());
        $statusCode = $result['success'] ? 201 : 400;
        return response()->json($result, $statusCode);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = array_merge($request->all(), ['id' => $id]);
        $result = $this->tools->update($data);
        $statusCode = $result['success'] ? 200 : 400;
        return response()->json($result, $statusCode);
    }

    public function destroy(int $id): JsonResponse
    {
        $result = $this->tools->delete($id);
        $statusCode = $result['success'] ? 200 : 400;
        return response()->json($result, $statusCode);
    }

    /**
     * Convert quote to invoice
     */
    public function convertToInvoice(int $id): JsonResponse
    {
        $result = $this->tools->convertToInvoice(['id' => $id]);
        $statusCode = $result['success'] ? 201 : 400;
        return response()->json($result, $statusCode);
    }
}
