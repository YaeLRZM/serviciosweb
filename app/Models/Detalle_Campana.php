<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detalle_Campana extends Model
{
    /** @use HasFactory<\Database\Factories\DetalleCampanaFactory> */
    use HasFactory;
    protected $fillable = [
        'campana_id',
        'articulo_id',
        'categoria_id',
        'porcentaje_descuento',
        'precio_fijo_oferta',
    ];

    public function campana()
    {
        return $this->belongsTo(Campana::class);
    }
    public function articulo()
    {
        return $this->belongsTo(Articulo::class);
    }
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
}
