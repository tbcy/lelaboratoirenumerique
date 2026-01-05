<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Mcp\Resources\SocialPostResource;
use App\Services\Mcp\Tools\SocialPostTools;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialPostController extends Controller
{
    public function __construct(
        private SocialPostResource $resource,
        private SocialPostTools $tools
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'page', 'per_page']);
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

    /**
     * Approve a social post
     */
    public function approve(int $id): JsonResponse
    {
        $result = $this->tools->approve(['id' => $id]);
        $statusCode = $result['success'] ? 200 : 400;
        return response()->json($result, $statusCode);
    }

    /**
     * Publish a social post
     */
    public function publish(int $id): JsonResponse
    {
        $result = $this->tools->publish(['id' => $id]);
        $statusCode = $result['success'] ? 200 : 400;
        return response()->json($result, $statusCode);
    }
}
