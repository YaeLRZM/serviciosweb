<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ArticleController;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

Route::post('/debug', function (\Illuminate\Http\Request $request) {
    return response()->json([
        'method' => $request->method(),
        'path' => $request->path(),
        'url' => $request->url(),
        'headers' => $request->headers->all(),
        'body' => $request->all(),
    ]);
});

Route::prefix('auth')->group(function () {
    Route::post('register', function (\Illuminate\Http\Request $request) {
        $storage = new \App\Services\JsonStorageService();
        $input = $request->all();

        if (empty($input['nombre']) || empty($input['password']) || empty($input['rol'])) {
            return response()->json(['error' => 'Missing required fields'], 400);
        }

        $users = $storage->read('users.json');

        foreach ($users as $user) {
            if ($user['nombre'] === $input['nombre']) {
                return response()->json(['message' => 'El usuario ya existe'], 400);
            }
        }

        $newUser = [
            'id' => empty($users) ? 1 : max(array_column($users, 'id')) + 1,
            'nombre' => $input['nombre'],
            'password' => \Illuminate\Support\Facades\Hash::make($input['password']),
            'rol' => $input['rol'],
        ];

        $users[] = $newUser;
        $storage->write('users.json', $users);

        $response = $newUser;
        unset($response['password']);

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'user' => $response,
        ], 201);
    });
    Route::post('login', function (\Illuminate\Http\Request $request) {
        $storage = new \App\Services\JsonStorageService();
        $input = $request->all();

        $users = $storage->read('users.json');
        $user = null;

        foreach ($users as $u) {
            if ($u['nombre'] === $input['nombre']) {
                $user = $u;
                break;
            }
        }

        if (!$user || !\Illuminate\Support\Facades\Hash::check($input['password'], $user['password'])) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        $userModel = new \App\Models\User();
        $userModel->id = $user['id'];
        $userModel->nombre = $user['nombre'];
        $userModel->rol = $user['rol'];

        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($userModel);

        return response()->json([
            'message' => 'Login exitoso',
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'rol' => $user['rol'],
            ],
        ], 200);
    });

    Route::post('logout', function (\Illuminate\Http\Request $request) {
        try {
            \Tymon\JWTAuth\Facades\JWTAuth::invalidate(\Tymon\JWTAuth\Facades\JWTAuth::getToken());
            return response()->json(['message' => 'Logout exitoso'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    });
});

// Rutas públicas (solo lectura)
Route::get('/users', function () {
    $storage = new \App\Services\JsonStorageService();
    $users = $storage->read('users.json');
    return response()->json(
        collect($users)->map(function ($user) {
            unset($user['password']);
            return $user;
        })->values()->all()
    );
});

Route::get('/users/{id}', function ($id) {
    $storage = new \App\Services\JsonStorageService();
    $users = $storage->read('users.json');
    $user = collect($users)->firstWhere('id', (int)$id);

    if (!$user) {
        return response()->json(['message' => 'Usuario no encontrado'], 404);
    }

    unset($user['password']);
    return response()->json($user);
});

Route::get('/articles', function () {
    $storage = new \App\Services\JsonStorageService();
    return response()->json($storage->read('articles.json'));
});

Route::get('/articles/{id}', function ($id) {
    $storage = new \App\Services\JsonStorageService();
    $articles = $storage->read('articles.json');
    $article = collect($articles)->firstWhere('id', (int)$id);

    if (!$article) {
        return response()->json(['message' => 'Artículo no encontrado'], 404);
    }

    return response()->json($article);
});

// Rutas protegidas con autenticación JWT
Route::middleware('auth:api')->group(function () {
    // Rutas protegidas: solo ADMIN puede crear, actualizar y eliminar
    Route::middleware('role:admin')->group(function () {
        // Usuarios
        Route::put('/users/{id}', function (\Illuminate\Http\Request $request, $id) {
            $storage = new \App\Services\JsonStorageService();
            $users = $storage->read('users.json');
            $input = $request->all();

            foreach ($users as &$user) {
                if ($user['id'] == $id) {
                    if (!empty($input['nombre'])) $user['nombre'] = $input['nombre'];
                    if (!empty($input['password'])) $user['password'] = \Illuminate\Support\Facades\Hash::make($input['password']);
                    if (!empty($input['rol'])) $user['rol'] = $input['rol'];

                    $storage->write('users.json', $users);

                    unset($user['password']);
                    return response()->json([
                        'message' => 'Usuario actualizado',
                        'user' => $user,
                    ], 200);
                }
            }

            return response()->json(['message' => 'Usuario no encontrado'], 404);
        });

        Route::delete('/users/{id}', function ($id) {
            $storage = new \App\Services\JsonStorageService();
            $users = $storage->read('users.json');

            $newUsers = array_filter($users, fn($u) => $u['id'] != $id);
            if (count($newUsers) === count($users)) {
                return response()->json(['message' => 'Usuario no encontrado'], 404);
            }

            $storage->write('users.json', array_values($newUsers));
            return response()->json(['message' => 'Usuario eliminado'], 200);
        });

        // Artículos
        Route::post('/articles', function (\Illuminate\Http\Request $request) {
            $storage = new \App\Services\JsonStorageService();
            $input = $request->all();

            if (empty($input['nombre']) || empty($input['precio'])) {
                return response()->json(['error' => 'Missing required fields'], 400);
            }

            $articles = $storage->read('articles.json');

            $article = [
                'id' => empty($articles) ? 1 : max(array_column($articles, 'id')) + 1,
                'nombre' => $input['nombre'],
                'precio' => (float)$input['precio'],
            ];

            $articles[] = $article;
            $storage->write('articles.json', $articles);

            return response()->json([
                'message' => 'Artículo creado exitosamente',
                'article' => $article,
            ], 201);
        });

        Route::put('/articles/{id}', function (\Illuminate\Http\Request $request, $id) {
            $storage = new \App\Services\JsonStorageService();
            $articles = $storage->read('articles.json');
            $input = $request->all();

            foreach ($articles as &$article) {
                if ($article['id'] == $id) {
                    if (!empty($input['nombre'])) $article['nombre'] = $input['nombre'];
                    if (!empty($input['precio'])) $article['precio'] = (float)$input['precio'];

                    $storage->write('articles.json', $articles);

                    return response()->json([
                        'message' => 'Artículo actualizado',
                        'article' => $article,
                    ], 200);
                }
            }

            return response()->json(['message' => 'Artículo no encontrado'], 404);
        });

        Route::delete('/articles/{id}', function ($id) {
            $storage = new \App\Services\JsonStorageService();
            $articles = $storage->read('articles.json');

            $newArticles = array_filter($articles, fn($a) => $a['id'] != $id);
            if (count($newArticles) === count($articles)) {
                return response()->json(['message' => 'Artículo no encontrado'], 404);
            }

            $storage->write('articles.json', array_values($newArticles));
            return response()->json(['message' => 'Artículo eliminado'], 200);
        });
    });
});
