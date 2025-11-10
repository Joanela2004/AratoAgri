<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth('sanctum')->check()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        if (!auth('sanctum')->user()?->role) {
            return response()->json(['error' => 'Forbidden. Admin role required.'], 403);
        }

        return $next($request);
    }
}
