<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    /** @use HasFactory<\Database\Factories\InventarioFactory> */
    use HasFactory;
    protected $fillable = [
        'articulo_id',
        'stock_actual',
        'stock_minimo'
    ];
    public function articulo()
    {
        return $this->belongsTo(Articulo::class);
    }
    public function detalle_inventarios()
    {
        return $this->hasMany(DetalleInventario::class);
    }
}
