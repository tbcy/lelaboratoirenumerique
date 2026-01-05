<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Mcp\Resources\SocialConnectionResource;
use App\Services\Mcp\Tools\SocialConnectionTools;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialConnectionController extends Controller
{
    public function __construct(
        private SocialConnectionResource $resource,
        private SocialConnectionTools $tools
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['platform', 'is_active', 'page', 'per_page']);
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
}
