<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Forma_Pago extends Model
{
    /** @use HasFactory<\Database\Factories\FormaPagoFactory> */
    use HasFactory;

    protected $fillable = [
        'nombre',
    ];
    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }
}
