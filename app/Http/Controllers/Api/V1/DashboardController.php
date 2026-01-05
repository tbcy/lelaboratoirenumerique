<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Mcp\Resources\DashboardResource;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardResource $resource
    ) {}

    /**
     * Get dashboard statistics
     */
    public function index(): JsonResponse
    {
        $result = $this->resource->getDashboard([]);
        return response()->json($result);
    }
}
