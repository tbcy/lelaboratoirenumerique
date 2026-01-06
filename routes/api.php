<?php

use App\Http\Controllers\Api\NoteApiController;
use App\Http\Controllers\Api\V1\CatalogCategoryController;
use App\Http\Controllers\Api\V1\CatalogItemController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\QuoteController;
use App\Http\Controllers\Api\V1\SocialConnectionController;
use App\Http\Controllers\Api\V1\SocialPostController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\TimeEntryController;
use App\Http\Controllers\Mcp\McpStreamableController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| MCP Endpoint
|--------------------------------------------------------------------------
*/
Route::post('/mcp', [McpStreamableController::class, 'handle'])
    ->middleware('mcp.auth');

/*
|--------------------------------------------------------------------------
| RESTful HTTP API Routes (v1)
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->middleware(['mcp.auth', 'mcp.logging'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Clients
    Route::get('/clients', [ClientController::class, 'index']);
    Route::get('/clients/{id}', [ClientController::class, 'show']);
    Route::post('/clients', [ClientController::class, 'store']);
    Route::put('/clients/{id}', [ClientController::class, 'update']);
    Route::delete('/clients/{id}', [ClientController::class, 'destroy']);

    // Invoices
    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
    Route::post('/invoices', [InvoiceController::class, 'store']);
    Route::put('/invoices/{id}', [InvoiceController::class, 'update']);
    Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy']);
    Route::post('/invoices/{id}/mark-paid', [InvoiceController::class, 'markPaid']);

    // Quotes
    Route::get('/quotes', [QuoteController::class, 'index']);
    Route::get('/quotes/{id}', [QuoteController::class, 'show']);
    Route::post('/quotes', [QuoteController::class, 'store']);
    Route::put('/quotes/{id}', [QuoteController::class, 'update']);
    Route::delete('/quotes/{id}', [QuoteController::class, 'destroy']);
    Route::post('/quotes/{id}/convert-to-invoice', [QuoteController::class, 'convertToInvoice']);

    // Tasks
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{id}/status', [TaskController::class, 'updateStatus']);
    Route::post('/tasks/{id}/log-time', [TaskController::class, 'logTime']);

    // Projects
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::put('/projects/{id}', [ProjectController::class, 'update']);
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);

    // Time Entries
    Route::get('/time-entries', [TimeEntryController::class, 'index']);
    Route::get('/time-entries/{id}', [TimeEntryController::class, 'show']);
    Route::post('/time-entries', [TimeEntryController::class, 'store']);
    Route::post('/time-entries/{id}/stop', [TimeEntryController::class, 'stop']);
    Route::delete('/time-entries/{id}', [TimeEntryController::class, 'destroy']);

    // Social Posts
    Route::get('/social-posts', [SocialPostController::class, 'index']);
    Route::get('/social-posts/{id}', [SocialPostController::class, 'show']);
    Route::post('/social-posts', [SocialPostController::class, 'store']);
    Route::put('/social-posts/{id}', [SocialPostController::class, 'update']);
    Route::post('/social-posts/{id}/approve', [SocialPostController::class, 'approve']);
    Route::post('/social-posts/{id}/publish', [SocialPostController::class, 'publish']);

    // Social Connections
    Route::get('/social-connections', [SocialConnectionController::class, 'index']);
    Route::get('/social-connections/{id}', [SocialConnectionController::class, 'show']);
    Route::post('/social-connections', [SocialConnectionController::class, 'store']);
    Route::put('/social-connections/{id}', [SocialConnectionController::class, 'update']);
    Route::delete('/social-connections/{id}', [SocialConnectionController::class, 'destroy']);

    // Catalog Categories
    Route::get('/catalog-categories', [CatalogCategoryController::class, 'index']);
    Route::get('/catalog-categories/{id}', [CatalogCategoryController::class, 'show']);
    Route::post('/catalog-categories', [CatalogCategoryController::class, 'store']);
    Route::put('/catalog-categories/{id}', [CatalogCategoryController::class, 'update']);
    Route::delete('/catalog-categories/{id}', [CatalogCategoryController::class, 'destroy']);

    // Catalog Items
    Route::get('/catalog-items', [CatalogItemController::class, 'index']);
    Route::get('/catalog-items/{id}', [CatalogItemController::class, 'show']);
    Route::post('/catalog-items', [CatalogItemController::class, 'store']);
    Route::put('/catalog-items/{id}', [CatalogItemController::class, 'update']);
    Route::delete('/catalog-items/{id}', [CatalogItemController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Note API (External Integration)
|--------------------------------------------------------------------------
|
| Simple API for external applications to create notes.
| Uses a separate API token (NOTE_API_TOKEN).
|
*/
Route::prefix('notes')->middleware('note.api.auth')->group(function () {
    Route::post('/', [NoteApiController::class, 'store']);
    Route::get('/stakeholders', [NoteApiController::class, 'listStakeholders']);
    Route::get('/scopes', [NoteApiController::class, 'listScopes']);
});
