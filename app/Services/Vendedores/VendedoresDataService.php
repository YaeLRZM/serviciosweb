<?php

namespace App\Services\Vendedores;

use App\Models\Vendedor;

class VendedoresDataService
{
    /**
     * Listado real desde PostgreSQL (vendedors + tienda + user).
     */
    public function all(): array
    {
        return Vendedor::query()
            ->with(['tienda', 'user'])
            ->latest()
            ->get()
            ->map(fn (Vendedor $v) => $this->mapear($v))
            ->all();
    }

    /**
     * "Cola" = vendedores inactivos (pendientes de activar).
     */
    public function solicitudes(): array
    {
        return Vendedor::query()
            ->with(['tienda', 'user'])
            ->where('estatus', 'inactivo')
            ->latest()
            ->get()
            ->map(fn (Vendedor $v) => $this->mapear($v))
            ->all();
    }

    public function find(int $id): ?array
    {
        $vendedor = Vendedor::query()->with(['tienda', 'user'])->find($id);

        return $vendedor ? $this->mapear($vendedor) : null;
    }

    /**
     * Actualiza estatus real: activo | inactivo.
     */
    public function actualizarEstatus(int $id, string $estatus): void
    {
        $estatus = mb_strtolower(trim($estatus));
        if (! in_array($estatus, ['activo', 'inactivo'], true)) {
            throw new \InvalidArgumentException('Estatus inválido. Use activo o inactivo.');
        }

        $vendedor = Vendedor::findOrFail($id);
        $vendedor->update(['estatus' => $estatus]);
    }

    /**
     * @return array{total:int, inactivos:int, activos:int}
     */
    public function stats(): array
    {
        $total = Vendedor::query()->count();
        $inactivos = Vendedor::query()->where('estatus', 'inactivo')->count();
        $activos = Vendedor::query()->where('estatus', 'activo')->count();

        return [
            'total' => $total,
            'inactivos' => $inactivos,
            'activos' => $activos,
        ];
    }

    /**
     * Contrato de vista alineado al schema real.
     *
     * @return array{
     *   id:int,
     *   tienda:string,
     *   propietario:string,
     *   email:string,
     *   codigo_ine:string,
     *   foto_frontal_ine_link:string,
     *   foto_trasera_ine_link:string,
     *   imagen:string,
     *   estatus:string,
     *   created_at:?string,
     *   ingreso:string
     * }
     */
    protected function mapear(Vendedor $v): array
    {
        $tienda = $v->tienda;
        $user = $v->user;

        // nombre_completo es accessor en User (nombre + apellidos; fallback email)
        $propietario = $user ? (string) $user->nombre_completo : 'Sin usuario';
        if ($propietario === '') {
            $propietario = 'Sin nombre';
        }

        $email = (string) ($user->email ?? '');
        $nombreTienda = (string) ($tienda->nombre ?? 'Sin tienda');
        $fotoFrontal = (string) ($v->foto_frontal_ine_link ?? '');
        $fotoTrasera = (string) ($v->foto_trasera_ine_link ?? '');

        $estatus = mb_strtolower((string) $v->estatus);
        if (! in_array($estatus, ['activo', 'inactivo'], true)) {
            $estatus = (string) $v->estatus;
        }

        $ingreso = $v->created_at
            ? $v->created_at->translatedFormat('d M Y')
            : '—';

        return [
            'id' => (int) $v->id,
            'tienda' => $nombreTienda,
            'propietario' => $propietario,
            'email' => $email,
            'codigo_ine' => (string) ($v->codigo_ine ?? ''),
            'foto_frontal_ine_link' => $fotoFrontal,
            'foto_trasera_ine_link' => $fotoTrasera,
            // Alias de visualización (foto INE frontal o avatar)
            'imagen' => $fotoFrontal !== ''
                ? $fotoFrontal
                : 'https://ui-avatars.com/api/?name=' . urlencode($nombreTienda) . '&background=D81B60&color=fff',
            'estatus' => $estatus,
            'created_at' => $v->created_at?->toIso8601String(),
            'ingreso' => $ingreso,
        ];
    }
}
