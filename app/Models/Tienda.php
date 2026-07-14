<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tienda extends Model
{
    /** @use HasFactory<\Database\Factories\TiendaFactory> */
    use HasFactory;
    protected $fillable = [
        'nombre',
        'rfc_moral',
        'descripcion',
    ];

    public function articulos()
    {
        return $this->hasMany(Articulo::class);
    }
}
