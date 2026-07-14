<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuponCanjeado extends Model
{
    /** @use HasFactory<\Database\Factories\CuponCanjeadoFactory> */
    use HasFactory;
    protected $fillable = [
        'cupon_id',
        'user_id',
        'venta_id',
        'monto_descuento',
        'fecha_canje',
    ];

    public function cupon()
    {
        return $this->belongsTo(Cupon::class);
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
