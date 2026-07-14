<?php

namespace App\Livewire\Concerns;

use Tymon\JWTAuth\Facades\JWTAuth;

trait RequiresApiAuth
{
    public function bootRequiresApiAuth()
    {
        if (! session('api_token') && auth()->check()) {
            session(['api_token' => JWTAuth::fromUser(auth()->user())]);
        }
    }
}
