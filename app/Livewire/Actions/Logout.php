<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tymon\JWTAuth\Facades\JWTAuth;

class Logout
{
    /**
     * Log the current user out of the application.
     */
    public function __invoke(): void
    {
        if (session('api_token')) {
            try {
                JWTAuth::setToken(session('api_token'))->invalidate();
            } catch (\Throwable $e) {
                // El token ya pudo haber expirado o estar blacklisteado, no pasa nada
            }
        }

        session()->forget(['api_token', 'api_user']);

        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();

        redirect()->to('/');
    }
}
