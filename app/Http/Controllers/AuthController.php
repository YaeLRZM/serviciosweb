<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    /**
     * Registro público de comprador (rol Spatie `user`).
     * No crea vendedores ni admins.
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'nombre' => $data['nombre'],
            'apellido_paterno' => $data['apellido_paterno'],
            'apellido_materno' => $data['apellido_materno'] ?? null,
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $user->assignRole('user');

        $token = Auth::guard('api')->login($user);

        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'role' => 'user',
            'user' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'email' => $user->email,
            ],
        ], 201);
    }

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
     * Incluye vendedor.tienda cuando aplica (panel "Mis productos").
     */
    public function me()
    {
        $user = auth('api')->user();
        if (! $user) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Acceso denegado. Por favor, inicie sesión para continuar.',
            ], 401);
        }

        $user->load(['vendedor.tienda']);
        $role = $user->getRoleNames()->first() ?? 'user';

        // Rol explícito para la app (guards de catálogo: carrito/compra/reseñas).
        $payload = $user->toArray();
        $payload['role'] = $role;
        $payload['rol'] = $role;

        return response()->json($payload);
    }

    /**
     * Actualizar perfil del usuario autenticado.
     * El rol NUNCA se actualiza desde aquí (solo admin en su panel).
     */
    public function updateProfile(Request $request)
    {
        $user = auth('api')->user();
        if (! $user) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Acceso denegado. Por favor, inicie sesión para continuar.',
            ], 401);
        }

        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'apellido_paterno' => ['sometimes', 'required', 'string', 'max:255'],
            'apellido_materno' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email,'.$user->id,
            ],
            'telefono' => ['sometimes', 'nullable', 'string', 'max:40'],
            'direccion' => ['sometimes', 'nullable', 'string', 'max:500'],
            'foto_url' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ]);

        // Defensa en profundidad: ignorar cualquier intento de cambiar rol.
        unset($data['rol'], $data['role'], $data['roles'], $data['password']);

        $user->fill($data);
        $user->save();
        $user->load(['vendedor.tienda']);

        return response()->json([
            'message' => 'Perfil actualizado',
            'user' => $user,
        ]);
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
