<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        //verifions si l utilisateur est authentifié de role admin
        if(!$request->user() || $request->user()->role !=='admin'){
            return response()->json(['error','Acces refusé']);
        }
        return $next($request);
    }
}
