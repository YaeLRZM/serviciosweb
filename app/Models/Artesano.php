<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artesano extends Model
{
    /** @use HasFactory<\Database\Factories\ArtesanoFactory> */
    use HasFactory;
    protected $fillable = ['nombre'];

    public function articulos()
    {
        return $this->hasMany(Articulo::class);
    }
}
