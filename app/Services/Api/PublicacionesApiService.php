<?php

namespace App\Services\Api;

class PublicacionApiService extends ApiClient
{
    public function reportadas()
    {
        return $this->get('/publicaciones/reportadas');
    }

    public function find(int $id)
    {
        return $this->get("/publicaciones/{$id}");
    }

    public function actualizarEstado(int $id, string $estado)
    {
        return $this->patch("/publicaciones/{$id}/estado", ['estado' => $estado]);
    }
}
