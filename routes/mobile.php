<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::prefix('mobile')->name('mobile.')->group(function () {
    Volt::route('/', 'mobile.welcome')
        ->name('welcome');

    Route::middleware('guest')->group(function () {
        Volt::route('register', 'mobile.auth.register')
            ->name('register');

        Volt::route('login', 'mobile.auth.login')
            ->name('login');
    });

    Route::middleware(['auth', 'verified'])->group(function () {
        Volt::route('dashboard', 'mobile.dashboard')
            ->name('dashboard');
    });
});
