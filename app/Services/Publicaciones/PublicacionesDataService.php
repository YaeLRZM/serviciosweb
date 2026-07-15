<?php
// app/Services/Publicaciones/PublicacionesDataService.php
namespace App\Services\Publicaciones;

use App\Services\Api\PublicacionApiService;
use App\Services\Api\ResenaApiService;
use App\Support\Mock\PublicacionesMock;

class PublicacionesDataService
{
    public function __construct(
        protected PublicacionApiService $api,
        protected ResenaApiService $resenas,
    ) {}

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

        $items = $respuesta->json('data', []);

        return $this->conCalificacionPromedio($items);
    }

    public function find(int $id): ?array
    {
        if ($this->usarMock()) {
            return PublicacionesMock::find($id);
        }

        $respuesta = $this->api->find($id);

        if (! $respuesta->successful()) {
            return null;
        }

        $publicacion = $respuesta->json('data');

        if (! $publicacion) {
            return null;
        }

        $publicacion['calificacion_promedio'] = $this->promedioDe((int) $publicacion['id']);

        return $publicacion;
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

    /**
     * Agrega 'calificacion_promedio' a cada item, calculado a partir de
     * TODAS las reseñas del sistema (una sola llamada, sin N+1).
     */
    private function conCalificacionPromedio(array $items): array
    {
        $promedios = $this->obtenerPromediosPorArticulo();

        return array_map(function ($item) use ($promedios) {
            $item['calificacion_promedio'] = $promedios[$item['id']] ?? 0;
            return $item;
        }, $items);
    }

    private function promedioDe(int $articuloId): float
    {
        return $this->obtenerPromediosPorArticulo()[$articuloId] ?? 0;
    }

    private function obtenerPromediosPorArticulo(): array
    {
        $respuesta = $this->resenas->all();

        if (! $respuesta->successful()) {
            // Si las reseñas fallan, no rompemos la vista completa —
            // simplemente no mostramos calificación (se ve como 0 estrellas).
            return [];
        }

        $resenas = collect($respuesta->json('data', []));

        return $resenas
            ->groupBy('articulo_id')
            ->map(fn($grupo) => round($grupo->avg('calificacion'), 1))
            ->toArray();
    }
}
