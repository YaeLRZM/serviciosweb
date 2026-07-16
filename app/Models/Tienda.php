<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tienda extends Model
{
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
    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }
    public function campanas()
    {
        return $this->hasMany(Campana::class);
    }
    public function cupons()
    {
        return $this->hasMany(Cupon::class);
    }

    public function vendedors()
    {
        return $this->hasMany(Vendedor::class);
    }
}
