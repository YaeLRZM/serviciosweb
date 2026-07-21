<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasRoles, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'password',
        'telefono',
        'direccion',
        'foto_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @var list<string>
     */
    protected $appends = [
        'rol',
    ];

    public function getRolAttribute(): ?string
    {
        return $this->getRoleNames()->first();
    }

    /**
     * Nombre completo: nombre + apellido_paterno + apellido_materno (nullable).
     * Fallback a email si no hay partes.
     */
    public function getNombreCompletoAttribute(): string
    {
        $partes = array_filter([
            filled($this->nombre) ? (string) $this->nombre : null,
            filled($this->apellido_paterno) ? (string) $this->apellido_paterno : null,
            filled($this->apellido_materno) ? (string) $this->apellido_materno : null,
        ]);

        $completo = trim(implode(' ', $partes));

        return $completo !== '' ? $completo : (string) $this->email;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function carrito()
    {
        return $this->hasOne(Carrito::class);
    }

    /** Perfil de vendedor (si el usuario tiene rol vendedor). */
    public function vendedor()
    {
        return $this->hasOne(Vendedor::class);
    }

    public function resenas()
    {
        return $this->hasMany(Resena::class);
    }
    public function direcciones()
    {
        return $this->hasMany(Direccion::class);
    }
    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }
    public function detalle_inventarios()
    {
        return $this->hasMany(DetalleInventario::class);
    }
    public function cuponCanjeados()
    {
        return $this->hasMany(CuponCanjeado::class);
    }
    
    /**
     * Obtiene el identificador que se almacenará en el "subject" (sub) del JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Devuelve un arreglo de claims personalizados para añadir al JWT.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

}
