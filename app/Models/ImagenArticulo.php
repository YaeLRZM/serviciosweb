<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImagenArticulo extends Model
{
    /** @use HasFactory<\Database\Factories\ImagenArticuloFactory> */
    use HasFactory;
    protected $fillable = [
        'articulo_id',
        'url',
        'es_principal',
    ];
    public function articulo()
    {
        return $this->belongsTo(Articulo::class);
    }
}
