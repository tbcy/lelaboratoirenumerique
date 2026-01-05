<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Mcp\Resources\TaskResource;
use App\Services\Mcp\Tools\TaskTools;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private TaskResource $resource,
        private TaskTools $tools
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'priority', 'project_id', 'client_id', 'overdue', 'page', 'per_page']);
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

    /**
     * Update task status
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $status = $request->input('status');
        $result = $this->tools->updateStatus($id, $status);
        $statusCode = $result['success'] ? 200 : 400;
        return response()->json($result, $statusCode);
    }

    /**
     * Log time to a task
     */
    public function logTime(Request $request, int $id): JsonResponse
    {
        $result = $this->tools->logTime($id, $request->all());
        $statusCode = $result['success'] ? 201 : 400;
        return response()->json($result, $statusCode);
    }
}
