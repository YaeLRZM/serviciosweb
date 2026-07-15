<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artesano extends Model
{
    /** @use HasFactory<\Database\Factories\ArtesanoFactory> */
    use HasFactory;
    protected $fillable = ['nombre'];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function articulos()
    {
        return $this->hasMany(Articulo::class);
    }
}
