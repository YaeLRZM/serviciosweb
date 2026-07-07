<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        if (!in_array($user->rol, $roles)) {
            return response()->json([
                'message' => 'No tienes permiso para realizar esta acción',
                'required_role' => $roles,
                'your_role' => $user->rol,
            ], 403);
        }

        return $next($request);
    }
}
