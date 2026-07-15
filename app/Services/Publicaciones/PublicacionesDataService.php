<?php

namespace App\Services\Publicaciones;

use App\Services\Api\PublicacionApiService;
use App\Support\Mock\PublicacionesMock;

class PublicacionesDataService
{
    public function __construct(protected PublicacionApiService $api) {}

    protected function usarMock(): bool
    {
        return (bool) config('features.mock_publicaciones', true);
    }

    /**
     * @throws \RuntimeException si el API falla y no estamos en modo mock
     */
    public function reportadas(): array
    {
        if ($this->usarMock()) {
            return PublicacionesMock::all();
        }

        $respuesta = $this->api->reportadas();

        if (! $respuesta->successful()) {
            throw new \RuntimeException('No se pudieron cargar las publicaciones reportadas.');
        }

        return $respuesta->json('data', []);
    }

    public function find(int $id): ?array
    {
        if ($this->usarMock()) {
            return PublicacionesMock::find($id);
        }

        $respuesta = $this->api->find($id);

        return $respuesta->successful() ? $respuesta->json('data') : null;
    }

    /**
     * @throws \RuntimeException si el API falla y no estamos en modo mock
     */
    public function actualizarEstado(int $id, string $estado): void
    {
        if ($this->usarMock()) {
            PublicacionesMock::actualizarEstado($id, $estado);
            return;
        }

        $respuesta = $this->api->actualizarEstado($id, $estado);

        if (! $respuesta->successful()) {
            throw new \RuntimeException('No se pudo actualizar el estado de la publicación.');
        }
    }
}
