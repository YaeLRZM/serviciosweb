<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticuloResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'precio' => $this->precio,
            'stock' => $this->stock,
            'disponible' => (bool) ($this->disponible ?? true),
            'talla' => $this->talla,
            'color' => $this->color,
            'bordado' => $this->bordado,
            'tela' => $this->tela,
            'region' => $this->region,

            'categoria' => $this->whenLoaded('categoria', fn () => [
                'id' => $this->categoria->id,
                'nombre' => $this->categoria->nombre,
                'descripcion' => $this->categoria->descripcion,
            ]),

            'artesano' => $this->whenLoaded('artesano', fn () => [
                'id' => $this->artesano->id,
                'nombre' => $this->artesano->nombre,
            ]),

            'tienda' => $this->whenLoaded('tienda', fn () => [
                'id' => $this->tienda->id,
                'nombre' => $this->tienda->nombre,
            ]),

            'imagenes' => $this->whenLoaded('imagenes', fn () => $this->imagenes->map(fn ($img) => [
                'id' => $img->id,
                'url' => $img->url,
                'es_principal' => $img->es_principal,
            ])),
        ];
    }
}
