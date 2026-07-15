<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Envio extends Model
{
    /** @use HasFactory<\Database\Factories\EnvioFactory> */
    use HasFactory;
    protected $fillable = [
        'venta_id',
        'direccion_id',
        'numero_guia',
        'paqueteria',
        'estado_envio',
        'fecha_envio',
        'fecha_entrega',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
    public function direccion()
    {
        return $this->belongsTo(Direccion::class);
    }
}
