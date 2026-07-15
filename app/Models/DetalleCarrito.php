<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleCarrito extends Model
{
    /** @use HasFactory<\Database\Factories\DetalleCarritoFactory> */
    use HasFactory;
    protected $fillable = [
        'carrito_id',
        'articulo_id',
        'cantidad',
        'precio_unitario',
    ];
    public function carrito()
    {
        return $this->belongsTo(Carrito::class);
    }
    public function articulo()
    {
        return $this->belongsTo(Articulo::class);
    }
}
