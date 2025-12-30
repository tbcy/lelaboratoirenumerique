<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Enable CORS for all requests
        $middleware->use([
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        // Exclude MCP endpoint from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'mcp',
            'api/*',
        ]);

        $middleware->alias([
            'mcp.auth' => \App\Http\Middleware\McpAuthentication::class,
            'mcp.logging' => \App\Http\Middleware\McpLogging::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
