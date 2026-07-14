<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Direccion extends Model
{
    /** @use HasFactory<\Database\Factories\DireccionFactory> */
    use HasFactory;
    protected $fillable = [
        'user_id',
        'estado_id',
        'calle',
        'colonia',
        'codigo_postal',
        'pais',
        'numero_exterior',
        'numero_interior'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }
}
