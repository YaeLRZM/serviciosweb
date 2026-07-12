<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('admin/dashboard', 'admin.dashboard')
    ->middleware(['auth', 'verified'])
    ->name('admin.dashboard');

Route::view('admin/publicaciones', 'admin.publicacion.index')
    ->middleware(['auth', 'verified'])
    ->name('admin.publicacion.index');

Route::view('user/dashboard', 'user.dashboard')
    ->middleware(['auth', 'verified'])
    ->name('user.dashboard');

Route::view('admin/profile', 'admin.profile')
    ->middleware(['auth'])
    ->name('admin.profile');

require __DIR__ . '/auth.php';
