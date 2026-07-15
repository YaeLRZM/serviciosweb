<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    /** @use HasFactory<\Database\Factories\VentaFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'forma_pago_id',
        'tienda_id',
        'total',
        'estado'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function forma_pago()
    {
        return $this->belongsTo(FormaPago::class);
    }
    public function tienda()
    {
        return $this->belongsTo(Tienda::class);
    }
    public function detalle_ventas()
    {
        return $this->hasMany(Detalle_Venta::class);
    }
    public function detalle_inventarios()
    {
        return $this->hasMany(Detalle_Inventario::class);
    }
    public function envio()
    {
        return $this->hasOne(Envio::class);
    }
    public function cuponCanjeados()
    {
        return $this->hasMany(CuponCanjeado::class);
    }
}
