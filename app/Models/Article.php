<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = ['id', 'nombre', 'precio'];

    protected $casts = [
        'id' => 'integer',
        'precio' => 'float',
    ];

    public static function fromArray(array $data): self
    {
        $article = new self();
        $article->id = $data['id'] ?? null;
        $article->nombre = $data['nombre'] ?? null;
        $article->precio = $data['precio'] ?? null;
        return $article;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'precio' => $this->precio,
        ];
    }
}
