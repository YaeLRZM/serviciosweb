<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    /**
     * Generar un JWT a partir de credenciales.
     */
    #[OA\Post(
        path: '/api/login',
        summary: 'Iniciar sesión y obtener un token JWT',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'test@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Autenticación correcta',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'access_token', type: 'string'),
                        new OA\Property(property: 'token_type', type: 'string', example: 'bearer'),
                        new OA\Property(property: 'expires_in', type: 'integer', example: 3600),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Credenciales inválidas'),
        ]
    )]
    
    public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    $credentials = $request->only('email', 'password');

    if (!$token = Auth::guard('api')->attempt($credentials)) {
        return response()->json(['error' => 'Credenciales inválidas'], 401);
    }

    $user = Auth::guard('api')->user();
    $role = $user->getRoleNames()->first() ?? 'cliente';

    // Bloquear acceso a Admin desde la App Móvil
    if ($role === 'admin') {
        Auth::guard('api')->logout(); // Cerramos la sesión
        return response()->json([
            'success' => false,
            'message' => 'Los administradores deben usar el panel web.',
            'role' => 'admin'
        ], 403);
    }

    return response()->json([
        'success' => true,
        'access_token' => $token,
        'token_type' => 'bearer',
        'expires_in' => config('jwt.ttl') * 60,
        'role' => $role,
        'user' => [
            'id' => $user->id,
            'nombre' => $user->nombre,
            'email' => $user->email,
        ]
    ]);
}

    /**
     * Obtener el usuario autenticado.
     */
    public function me()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Invalidar el token y desconectar al usuario (Logout).
     */
    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json(['message' => 'Sesión cerrada exitosamente']);
    }

    /**
     * Refrescar un token.
     */
    public function refresh()
    {
        return $this->respondWithToken(Auth::guard('api')->refresh());
    }

    /**
     * Estructura base para la respuesta del token.
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60 // Tiempo de expiración configurado
        ]);
    }
}
