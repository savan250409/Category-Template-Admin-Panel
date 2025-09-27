<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class JwtTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'Missing or invalid Authorization token.',
            ], 401);
        }

        $clientToken = trim(str_replace('Bearer', '', $authHeader));

        $validToken = env('MY_JWT_TOKEN');

        if ($clientToken !== $validToken) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid token.',
            ], 401);
        }

        return $next($request);
    }
}
