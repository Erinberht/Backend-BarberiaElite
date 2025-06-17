<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, $rol)
    {
        if (!$request->user() || $request->user()->rol !== $rol) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        return $next($request);
    }
} 