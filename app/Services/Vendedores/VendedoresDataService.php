<?php

namespace App\Services\Vendedores;

use App\Services\Api\VendedorApiService;
use App\Support\Mock\VendedoresMock;

class VendedoresDataService
{
    public function __construct(protected VendedorApiService $api) {}

    protected function usarMock(): bool
    {
        return (bool) config('features.mock_vendedores', true);
    }

    /**
     * Listado normalizado de vendedores para la tabla y estadísticas.
     *
     * @throws \RuntimeException si el API falla y no estamos en modo mock
     */
    public function all(): array
    {
        if ($this->usarMock()) {
            return VendedoresMock::all();
        }

        $respuesta = $this->api->all();

        if (! $respuesta->successful()) {
            throw new \RuntimeException('No se pudieron cargar los vendedores.');
        }

        return $this->normalizarLista($respuesta->json('data', $respuesta->json() ?? []));
    }

    /**
     * Solicitudes pendientes (estatus En Revisión).
     *
     * @throws \RuntimeException si el API falla y no estamos en modo mock
     */
    public function solicitudes(): array
    {
        if ($this->usarMock()) {
            return VendedoresMock::solicitudes();
        }

        $respuesta = $this->api->solicitudes();

        if (! $respuesta->successful()) {
            throw new \RuntimeException('No se pudieron cargar las solicitudes de vendedores.');
        }

        return $this->normalizarLista($respuesta->json('data', $respuesta->json() ?? []));
    }

    public function find(int $id): ?array
    {
        if ($this->usarMock()) {
            $item = VendedoresMock::find($id);

            return $item ? $this->normalizarItem($item) : null;
        }

        $respuesta = $this->api->find($id);

        if (! $respuesta->successful()) {
            return null;
        }

        $data = $respuesta->json('data', $respuesta->json());

        return $data ? $this->normalizarItem($data) : null;
    }

    /**
     * @throws \RuntimeException si el API falla y no estamos en modo mock
     */
    public function actualizarEstatus(int $id, string $estatus): void
    {
        if ($this->usarMock()) {
            VendedoresMock::actualizarEstatus($id, $estatus);

            return;
        }

        $respuesta = $this->api->actualizarEstatus($id, $estatus);

        if (! $respuesta->successful()) {
            throw new \RuntimeException('No se pudo actualizar el estatus del vendedor.');
        }
    }

    /**
     * Estadísticas derivadas del listado (sin endpoint dedicado).
     *
     * @return array{total:int, pendientes:int, marcados:int, activas:int}
     */
    public function stats(): array
    {
        $items = collect($this->all());

        return [
            'total' => $items->count(),
            'pendientes' => $items->where('estatus', 'En Revisión')->count(),
            'marcados' => $items->where(fn ($i) => $i['estatus'] === 'Suspendido' || ! empty($i['reportado']))->count(),
            'activas' => $items->where('estatus', 'Verificado')->count(),
        ];
    }

    /**
     * @param  array<int, mixed>  $items
     * @return array<int, array<string, mixed>>
     */
    protected function normalizarLista(array $items): array
    {
        return collect($items)
            ->map(fn ($item) => $this->normalizarItem(is_array($item) ? $item : (array) $item))
            ->values()
            ->all();
    }

    /**
     * Normaliza distintas formas de respuesta del API al contrato de la UI.
     *
     * Campos esperados del API (cualquiera de los alias se acepta):
     * - id
     * - tienda | nombre_tienda | store_name
     * - propietario | nombre | user.name
     * - email | correo | user.email
     * - imagen | foto | foto_url | avatar
     * - categoria | especialidad
     * - ingreso | created_at | fecha_ingreso
     * - rating | puntuacion | reputacion
     * - resenas | reseñas | reviews_count
     * - estatus | estado | status
     * - reportado | tiene_reporte | reportes_count > 0
     * - codigo_ine | ine
     * - foto_frontal_ine | foto_frontal_ine_link
     * - foto_trasera_ine | foto_trasera_ine_link
     *
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function normalizarItem(array $item): array
    {
        $user = is_array($item['user'] ?? null) ? $item['user'] : [];
        $tiendaRel = is_array($item['tienda'] ?? null) && ! is_string($item['tienda'] ?? null)
            ? $item['tienda']
            : [];

        $tienda = is_string($item['tienda'] ?? null)
            ? $item['tienda']
            : ($item['nombre_tienda']
                ?? $item['store_name']
                ?? $tiendaRel['nombre']
                ?? $tiendaRel['name']
                ?? 'Sin tienda');

        $propietario = $item['propietario']
            ?? $item['nombre']
            ?? $user['name']
            ?? $user['nombre']
            ?? 'Sin nombre';

        $reportado = $item['reportado']
            ?? $item['tiene_reporte']
            ?? ((int) ($item['reportes_count'] ?? $item['reportes'] ?? 0) > 0);

        $ingresoRaw = $item['ingreso'] ?? $item['fecha_ingreso'] ?? $item['created_at'] ?? null;
        $ingreso = $ingresoRaw;
        if ($ingresoRaw && preg_match('/^\d{4}-\d{2}-\d{2}/', (string) $ingresoRaw)) {
            try {
                $ingreso = \Illuminate\Support\Carbon::parse($ingresoRaw)->translatedFormat('d M Y');
            } catch (\Throwable) {
                $ingreso = (string) $ingresoRaw;
            }
        }

        $estatus = $item['estatus'] ?? $item['estado'] ?? $item['status'] ?? 'En Revisión';
        // Alias comunes del backend
        $estatus = match (mb_strtolower((string) $estatus)) {
            'verificado', 'aprobado', 'activo', 'active', 'approved' => 'Verificado',
            'en revisión', 'en revision', 'pendiente', 'pending', 'revision' => 'En Revisión',
            'suspendido', 'suspended', 'bloqueado' => 'Suspendido',
            'rechazado', 'rejected', 'denegado' => 'Rechazado',
            default => (string) $estatus,
        };

        return [
            'id' => (int) ($item['id'] ?? 0),
            'tienda' => (string) $tienda,
            'propietario' => (string) $propietario,
            'email' => (string) ($item['email'] ?? $item['correo'] ?? $user['email'] ?? ''),
            'imagen' => (string) ($item['imagen']
                ?? $item['foto']
                ?? $item['foto_url']
                ?? $item['avatar']
                ?? 'https://ui-avatars.com/api/?name=' . urlencode((string) $tienda) . '&background=D81B60&color=fff'),
            'categoria' => (string) ($item['categoria'] ?? $item['especialidad'] ?? $tiendaRel['categoria'] ?? '—'),
            'ingreso' => (string) ($ingreso ?? '—'),
            'rating' => isset($item['rating']) || isset($item['puntuacion']) || isset($item['reputacion'])
                ? (float) ($item['rating'] ?? $item['puntuacion'] ?? $item['reputacion'])
                : null,
            'resenas' => (int) ($item['resenas'] ?? $item['reseñas'] ?? $item['reviews_count'] ?? 0),
            'estatus' => $estatus,
            'reportado' => (bool) $reportado,
            'codigo_ine' => (string) ($item['codigo_ine'] ?? $item['ine'] ?? ''),
            'foto_frontal_ine' => $item['foto_frontal_ine'] ?? $item['foto_frontal_ine_link'] ?? null,
            'foto_trasera_ine' => $item['foto_trasera_ine'] ?? $item['foto_trasera_ine_link'] ?? null,
            'notas' => (string) ($item['notas'] ?? $item['notas_moderacion'] ?? ''),
        ];
    }
}
