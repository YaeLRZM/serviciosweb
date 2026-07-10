<?php

use App\Http\Controllers\ArticuloController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ResenaController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    if (! $token = auth('api')->attempt($credentials)) {
        return response()->json([
            'message' => 'Credenciales inválidas',
        ], 401);
    }

    return response()->json([
        'access_token' => $token,
        'token_type' => 'Bearer',
        'expires_in' => auth('api')->factory()->getTTL() * 60,
        'user' => auth('api')->user(),
    ]);
});

/*
|--------------------------------------------------------------------------
| Lectura pública (sin auth)
|--------------------------------------------------------------------------
*/
Route::get('/articulos', [ArticuloController::class, 'index'])->name('articulos.index');
Route::get('/articulos/{articulo}', [ArticuloController::class, 'show'])->name('articulos.show');
Route::get('/resenas', [ResenaController::class, 'index'])->name('resenas.index');
Route::get('/resenas/{resena}', [ResenaController::class, 'show'])->name('resenas.show');

/*
|--------------------------------------------------------------------------
| Rutas protegidas con JWT (auth:api)
| Incluye TODOS los destroy: DELETE /api/{recurso}/{id}
|--------------------------------------------------------------------------
*/
Route::middleware('auth:api')->group(function () {
    Route::get('/me', function () {
        return auth('api')->user();
    });

    Route::post('/logout', function () {
        auth('api')->logout();

        return response()->json([
            'message' => 'Sesión cerrada',
        ]);
    });

    Route::post('/refresh', function () {
        return response()->json([
            'access_token' => auth('api')->refresh(),
            'token_type' => 'Bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    });

    // Artículos: solo escritura (index/show son públicos arriba)
    // only() registra store + update + destroy (DELETE /articulos/{articulo})
    Route::apiResource('articulos', ArticuloController::class)
        ->only(['store', 'update', 'destroy']);

    // Reseñas: solo escritura (index/show son públicos arriba)
    // only() registra store + update + destroy (DELETE /resenas/{resena})
    Route::apiResource('resenas', ResenaController::class)
        ->only(['store', 'update', 'destroy']);

    // Usuarios: CRUD completo, incluido destroy (DELETE /usuarios/{usuario})
    Route::apiResource('usuarios', UserController::class);

    // Asignación de roles a usuarios (solo admin)
    Route::put('/usuarios/{usuario}/roles', [UserController::class, 'assignRoles']);

    // Compras: CRUD completo, incluido destroy (DELETE /compras/{compra})
    Route::apiResource('compras', CompraController::class);
});
