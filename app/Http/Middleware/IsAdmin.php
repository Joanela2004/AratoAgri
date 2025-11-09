<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || ($user->role ?? null) !== 'admin') {
            return response()->json(['message' => 'Accès refusé. Nécessite le rôle administrateur.'], 403);
        }

        return $next($request);
    }
}
