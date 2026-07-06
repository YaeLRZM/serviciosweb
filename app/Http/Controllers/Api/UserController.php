<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\JsonStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private $storage;

    public function __construct()
    {
        $this->storage = new JsonStorageService();
    }

    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Obtener lista de usuarios",
     *     tags={"Usuarios"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de usuarios",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="nombre", type="string"),
     *                 @OA\Property(property="rol", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function index()
    {
        $users = $this->storage->read('users.json');

        return response()->json(
            collect($users)->map(function ($user) {
                unset($user['password']);
                return $user;
            })->values()->all()
        );
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Obtener usuario por ID",
     *     tags={"Usuarios"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos del usuario",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="nombre", type="string"),
     *             @OA\Property(property="rol", type="string")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Usuario no encontrado")
     * )
     */
    public function show($id)
    {
        $users = $this->storage->read('users.json');
        $user = collect($users)->firstWhere('id', (int)$id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        unset($user['password']);

        return response()->json($user);
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Actualizar usuario",
     *     tags={"Usuarios"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nombre", type="string"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="rol", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario actualizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Usuario no encontrado")
     * )
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nombre' => 'string|max:255',
            'password' => 'string|min:6',
            'rol' => 'string|in:admin,user',
        ]);

        $users = $this->storage->read('users.json');
        $key = collect($users)->search(fn($u) => $u['id'] == $id);

        if ($key === false) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        if (isset($validated['nombre'])) {
            $users[$key]['nombre'] = $validated['nombre'];
        }

        if (isset($validated['password'])) {
            $users[$key]['password'] = Hash::make($validated['password']);
        }

        if (isset($validated['rol'])) {
            $users[$key]['rol'] = $validated['rol'];
        }

        $this->storage->write('users.json', $users);

        $user = $users[$key];
        unset($user['password']);

        return response()->json([
            'message' => 'Usuario actualizado',
            'user' => $user,
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Eliminar usuario",
     *     tags={"Usuarios"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario eliminado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Usuario no encontrado")
     * )
     */
    public function destroy($id)
    {
        $users = $this->storage->read('users.json');
        $key = collect($users)->search(fn($u) => $u['id'] == $id);

        if ($key === false) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        unset($users[$key]);
        $this->storage->write('users.json', array_values($users));

        return response()->json(['message' => 'Usuario eliminado'], 200);
    }
}
