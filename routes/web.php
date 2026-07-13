<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('admin/dashboard', 'admin.dashboard')
    ->middleware(['auth', 'verified'])
    ->name('admin.dashboard');

Route::view('user/dashboard', 'user.dashboard')
    ->middleware(['auth', 'verified'])
    ->name('user.dashboard');

Route::view('admin/profile', 'admin.profile')
    ->middleware(['auth'])
    ->name('admin.profile');

require __DIR__ . '/auth.php';
require __DIR__ . '/mobile.php';
