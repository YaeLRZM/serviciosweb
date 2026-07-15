<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendedorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo_ine' => $this->codigo_ine,
            'foto_frontal_ine_link' => $this->foto_frontal_ine_link,
            'foto_trasera_ine_link' => $this->foto_trasera_ine_link,
            'estatus' => $this->estatus,

            'tienda' => $this->whenLoaded('tienda', fn () => [
                'id' => $this->tienda->id,
                'nombre' => $this->tienda->nombre,
            ]),

            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'correo' => $this->user->correo,
            ]),
        ];
    }
}