<?php

use App\Http\Controllers\Mcp\McpStreamableController;
use Illuminate\Support\Facades\Route;

Route::post('/mcp', [McpStreamableController::class, 'handle'])
    ->middleware('mcp.auth');
