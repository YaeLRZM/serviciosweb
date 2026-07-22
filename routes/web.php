<?php

use App\Http\Controllers\ChatbotController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

/*
|--------------------------------------------------------------------------
| Mantener sesión activa (panel web)
|--------------------------------------------------------------------------
| Pings periódicos desde el admin evitan 419 por CSRF/sesión vencida
| cuando la pestaña queda abierta. Solo usuarios autenticados.
*/
Route::middleware(['auth', 'web'])->group(function () {
    Route::get('/session/keep-alive', function (Request $request) {
        // Tocar la sesión renueva last_activity en driver database.
        $request->session()->put('_last_keep_alive', now()->toIso8601String());

        return response()->json([
            'ok' => true,
            'csrf' => csrf_token(),
            'server_time' => now()->toIso8601String(),
        ]);
    })->name('session.keep-alive');
});

/*
|--------------------------------------------------------------------------
| Rutas de administración
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
        Route::view('/profile', 'admin.profile')->name('profile');

        Route::view('/publicaciones', 'admin.publicacion.index')->name('publicacion.index');

        Route::view('/categorias', 'admin.categoria.index')->name('categorias.index');

        Route::view('/artesanos', 'admin.artesano.index')->name('artesanos.index');

        Route::view('/usuarios', 'admin.usuario.index')->name('usuarios.index');

        Route::view('/vendedores', 'admin.vendedor.index')->name('vendedores.index');

        // Supervisión global (solo admin, middleware role:admin del grupo).
        Route::view('/ventas-generales', 'admin.ventas.index')->name('ventas.index');
        Route::view('/resenas', 'admin.resenas.index')->name('resenas.index');
    });

/*
|--------------------------------------------------------------------------
| Rutas de usuario / cliente
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {
        Route::view('/dashboard', 'user.dashboard')->name('dashboard');
    });

Route::match(['get', 'post'], '/chatbot', [ChatbotController::class, 'handle']);


require __DIR__ . '/auth.php';
