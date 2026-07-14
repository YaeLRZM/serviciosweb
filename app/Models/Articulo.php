<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Articulo extends Model
{
    /** @use HasFactory<\Database\Factories\ArticuloFactory> */
    use HasFactory;

    protected $fillable = [
        'categoria_id',
        'artesano_id',
        'tienda_id',
        'nombre',
        'talla',
        'color',
        'bordado',
        'tela',
        'region'
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function artesano()
    {
        return $this->belongsTo(Artesano::class);
    }

    public function tienda()
    {
        return $this->belongsTo(Tienda::class);
    }
    public function resenas()
    {
        return $this->hasMany(Resena::class);
    }
    public function inventario()
    {
        return $this->hasOne(Inventario::class);
    }
    public function detalle_ventas()
    {
        return $this->hasMany(Detalle_Venta::class);
    }
    public function detalle_carritos()
    {
        return $this->hasMany(DetalleCarrito::class);
    }
    public function detalle_campanas()
    {
        return $this->hasMany(DetalleCampana::class);
    }
    public function imagenes()
    {
        return $this->hasMany(ImagenArticulo::class);
    }
}
