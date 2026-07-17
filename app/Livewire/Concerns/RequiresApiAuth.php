<?php

namespace App\Livewire\Concerns;

use Tymon\JWTAuth\Facades\JWTAuth;

trait RequiresApiAuth
{
    public function bootRequiresApiAuth()
    {
        if (! auth()->check()) {
            return;
        }

        // Genera un token nuevo si no hay, o si el actual ya expiró/es inválido.
        // Sin esto, un token viejo en sesión (pasado el ttl de 60 min) provoca 401.
        if (! $this->apiTokenSigueValido()) {
            session(['api_token' => JWTAuth::fromUser(auth()->user())]);
        }
    }

    protected function apiTokenSigueValido(): bool
    {
        $token = session('api_token');

        if (! $token) {
            return false;
        }

        try {
            return (bool) JWTAuth::setToken($token)->check();
        } catch (\Throwable) {
            return false;
        }
    }
}
