<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArtesanoController;
use App\Http\Controllers\ArticuloController;
use App\Http\Controllers\CampanaController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CuponController;
use App\Http\Controllers\CuponCanjeadoController;
use App\Http\Controllers\DetalleCampanaController;
use App\Http\Controllers\DetalleCarritoController;
use App\Http\Controllers\DetalleInventarioController;
use App\Http\Controllers\DetalleVentaController;
use App\Http\Controllers\DireccionController;
use App\Http\Controllers\EnvioController;
use App\Http\Controllers\EstadoController;
use App\Http\Controllers\FormaPagoController;
use App\Http\Controllers\ImagenArticuloController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ResenaController;
use App\Http\Controllers\TiendaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendedorController;
use App\Http\Controllers\VentaController;

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
});
Route::apiResource('articulos', ArticuloController::class)->only(['index', 'show']);
Route::apiResource('categorias', CategoriaController::class)->only(['index', 'show']);
Route::apiResource('artesanos', ArtesanoController::class)->only(['index', 'show']);
Route::apiResource('tiendas', TiendaController::class)->only(['index', 'show']);

Route::apiResource('articulos', ArticuloController::class)->except(['index', 'show']);
Route::apiResource('categorias', CategoriaController::class)->except(['index', 'show']);
Route::apiResource('artesanos', ArtesanoController::class)->except(['index', 'show']);
Route::apiResource('tiendas', TiendaController::class)->except(['index', 'show']);

Route::apiResource('formas-pago', FormaPagoController::class);
Route::apiResource('estados', EstadoController::class);
Route::apiResource('usuarios', UserController::class);
Route::apiResource('vendedores', VendedorController::class);
Route::apiResource('resenas', ResenaController::class);
Route::apiResource('inventarios', InventarioController::class);
Route::apiResource('detalle-inventarios', DetalleInventarioController::class);
Route::apiResource('direcciones', DireccionController::class);
Route::apiResource('carritos', CarritoController::class);
Route::apiResource('detalle-carritos', DetalleCarritoController::class);
Route::apiResource('ventas', VentaController::class);
Route::apiResource('detalle-ventas', DetalleVentaController::class);
Route::apiResource('envios', EnvioController::class);
Route::apiResource('campanas', CampanaController::class);
Route::apiResource('detalle-campanas', DetalleCampanaController::class);
Route::apiResource('cupones', CuponController::class);
Route::apiResource('cupones-canjeados', CuponCanjeadoController::class);
Route::apiResource('imagen-articulos', ImagenArticuloController::class);
