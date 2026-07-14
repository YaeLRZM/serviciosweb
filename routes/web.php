<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

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
        Route::view('/categorias/{categoriaId}/edit', 'admin.categoria.edit')->name('categorias.edit');

        Route::view('/artesanos', 'admin.artesano.index')->name('artesanos.index');

        Route::view('/usuarios', 'admin.usuario.index')->name('usuarios.index');

        Route::view('/vendedores', 'admin.vendedor.index')->name('vendedores.index');
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

require __DIR__ . '/auth.php';
