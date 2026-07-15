<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campana extends Model
{
    /** @use HasFactory<\Database\Factories\CampanaFactory> */
    use HasFactory;
    protected $fillable = [
        'tienda_id',
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];
    
    public function tienda()
    {
        return $this->belongsTo(Tienda::class);
    }
    public function detalleCampanas()
    {
        return $this->hasMany(DetalleCampana::class);
    }
}
