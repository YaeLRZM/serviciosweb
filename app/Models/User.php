<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $fillable = ['id', 'nombre', 'password', 'rol'];

    protected $hidden = ['password'];

    protected $casts = [
        'id' => 'integer',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public static function fromArray(array $data): self
    {
        $user = new self();
        $user->id = $data['id'] ?? null;
        $user->nombre = $data['nombre'] ?? null;
        $user->password = $data['password'] ?? null;
        $user->rol = $data['rol'] ?? null;
        return $user;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'password' => $this->password,
            'rol' => $this->rol,
        ];
    }
}
