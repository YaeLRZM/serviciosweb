<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendedor extends Model
{
    /** @use HasFactory<\Database\Factories\VendedorFactory> */
    use HasFactory;
    protected $fillable = [
        'tienda_id',
        'user_id',
        'codigo_ine',
        'foto_frontal_ine_link',
        'foto_trasera_ine_link',
        'estatus'
    ];
    public function tienda()
    {
        return $this->belongsTo(Tienda::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
