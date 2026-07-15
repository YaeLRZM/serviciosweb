<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    /** @use HasFactory<\Database\Factories\DetalleVentaFactory> */
    use HasFactory;
    protected $fillable = [
        'venta_id',
        'articulo_id',
        'cantidad',
        'precio_unitario',
        'subtotal'
    ];
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
    public function articulo()
    {
        return $this->belongsTo(Producto::class);
    }
}
