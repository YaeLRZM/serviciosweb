<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resena extends Model
{
    /** @use HasFactory<\Database\Factories\ResenaFactory> */
    use HasFactory;
    protected $fillable = ['articulo_id', 'user_id', 'calificacion', 'comentario'];

    public function articulo()
    {
        return $this->belongsTo(Articulo::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
