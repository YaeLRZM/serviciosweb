<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleInventario extends Model
{
    /** @use HasFactory<\Database\Factories\DetalleInventarioFactory> */
    use HasFactory;
    protected $fillable = [
        'inventario_id',
        'user_id',
        'venta_id',
        'tipo_movimiento',
        'observaciones',
        'cantidad'
    ];

    public function inventario()
    {
        return $this->belongsTo(Inventario::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
}
