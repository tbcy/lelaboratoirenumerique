<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Mcp\Resources\TimeEntryResource;
use App\Services\Mcp\Tools\TimeEntryTools;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimeEntryController extends Controller
{
    public function __construct(
        private TimeEntryResource $resource,
        private TimeEntryTools $tools
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['task_id', 'user_id', 'running', 'page', 'per_page']);
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

    public function destroy(int $id): JsonResponse
    {
        $result = $this->tools->delete($id);
        $statusCode = $result['success'] ? 200 : 400;
        return response()->json($result, $statusCode);
    }

    /**
     * Stop a running time entry
     */
    public function stop(int $id): JsonResponse
    {
        $result = $this->tools->stop($id);
        $statusCode = $result['success'] ? 200 : 400;
        return response()->json($result, $statusCode);
    }
}
