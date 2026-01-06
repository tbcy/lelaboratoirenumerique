<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoteApiAuthentication
{
    /**
     * Handle an incoming request.
     *
     * Authenticates requests using a Bearer token from the NOTE_API_TOKEN env variable.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiToken = config('services.note_api.token');

        if (empty($apiToken)) {
            return response()->json([
                'success' => false,
                'message' => 'API not configured',
            ], 503);
        }

        $providedToken = $request->bearerToken();

        if (empty($providedToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization token required',
            ], 401);
        }

        if (! hash_equals($apiToken, $providedToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API token',
            ], 401);
        }

        return $next($request);
    }
}
