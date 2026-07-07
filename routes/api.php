<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticuloController;
use App\Http\Controllers\ResenaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompraController;

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

    // Artículos
    Route::apiResource('articulos', ArticuloController::class);
    
    // Reseñas
    Route::apiResource('resenas', ResenaController::class);
    
    // Usuarios
    Route::apiResource('usuarios', UserController::class);
    
    // Compras
    Route::apiResource('compras', CompraController::class);
});
