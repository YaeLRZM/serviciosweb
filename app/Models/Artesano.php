<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artesano extends Model
{
    /** @use HasFactory<\Database\Factories\ArtesanoFactory> */
    use HasFactory;
    protected $fillable = [
        'nombre',
        'especialidad',
        'foto',
        'ubicacion',
        'estado',
        'ventas_total',
        'ventas_items',
        'rating',
        'destacado',
        'notas_moderacion',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function articulos()
    {
        return $this->hasMany(Articulo::class);
    }
}
