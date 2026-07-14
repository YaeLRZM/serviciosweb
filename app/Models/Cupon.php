<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cupon extends Model
{
    /** @use HasFactory<\Database\Factories\CuponFactory> */
    use HasFactory;
    protected $fillable = [
        'tienda_id',
        'codigo',
        'porcentaje_descuento',
        'limite_uso',
        'fecha_expiracion',
        'compra_minima',
    ];
    public function tienda()
    {
        return $this->belongsTo(Tienda::class);
    }
    public function cuponCanjeados()
    {
        return $this->hasMany(CuponCanjeado::class);
    }
}
