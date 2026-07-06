<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\JsonStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    private JsonStorageService $storage;

    public function __construct()
    {
        $this->storage = new JsonStorageService();
    }

    public function register(Request $request)
    {
        try {
            return response()->json([
                'debug_all' => $request->all(),
                'debug_json' => $request->json(),
                'debug_json_all' => $request->json() ? $request->json()->all() : null,
            ], 200);

            $users = $this->storage->read('users.json');

            foreach ($users as $user) {
                if ($user['nombre'] === $input['nombre']) {
                    return response()->json(['message' => 'El usuario ya existe'], 400);
                }
            }

            $newUser = [
                'id' => empty($users) ? 1 : max(array_column($users, 'id')) + 1,
                'nombre' => $input['nombre'],
                'password' => Hash::make($input['password']),
                'rol' => $input['rol'],
            ];

            $users[] = $newUser;
            $this->storage->write('users.json', $users);

            $response = $newUser;
            unset($response['password']);

            return response()->json([
                'message' => 'Usuario registrado exitosamente',
                'user' => $response,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'nombre' => 'required|string',
                'password' => 'required|string',
            ]);

            $users = $this->storage->read('users.json');
            $user = null;

            foreach ($users as $u) {
                if ($u['nombre'] === $credentials['nombre']) {
                    $user = $u;
                    break;
                }
            }

            if (!$user || !Hash::check($credentials['password'], $user['password'])) {
                return response()->json(['message' => 'Credenciales inválidas'], 401);
            }

            $userModel = new User();
            $userModel->id = $user['id'];
            $userModel->nombre = $user['nombre'];
            $userModel->rol = $user['rol'];

            $token = JWTAuth::fromUser($userModel);

            return response()->json([
                'message' => 'Login exitoso',
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'nombre' => $user['nombre'],
                    'rol' => $user['rol'],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Logout exitoso'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
