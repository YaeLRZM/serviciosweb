<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\TiendaController;
use App\Http\Controllers\FormaPagoController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::apiResource('categorias', CategoriaController::class);
Route::apiResource('tiendas', TiendaController::class);
Route::apiResource('formas-pago', FormaPagoController::class);