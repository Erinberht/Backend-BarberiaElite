<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RolMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = auth()->user();

        if (!$user || !in_array($user->rol, $roles)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return $next($request);
    }
}

