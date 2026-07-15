<?php

namespace App\Services\Usuarios;

use App\Models\User;

class UsuariosDataService
{
    public function listar(array $filtros = []): array
    {
        $query = User::query()->with('roles');

        if (! empty($filtros['rol'])) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $filtros['rol']));
        }

        if (! empty($filtros['estatus'])) {
            $query->where('estatus', $filtros['estatus']);
        }

        if (! empty($filtros['busqueda'])) {
            $busqueda = $filtros['busqueda'];
            $query->where(fn ($q) => $q->where('name', 'like', "%{$busqueda}%")
                ->orWhere('email', 'like', "%{$busqueda}%"));
        }

        return $query->latest()->get()->toArray();
    }

    public function crear(array $data): array
    {
        $rol = $data['rol'] ?? 'user';
        unset($data['rol']);

        $usuario = User::create($data);
        $usuario->assignRole($rol);

        return $usuario->load('roles')->toArray();
    }

    public function find(int $id): ?array
    {
        $usuario = User::query()->with('roles')->find($id);

        return $usuario?->toArray();
    }

    /**
     * @throws \RuntimeException si la actualización falla
     */
    public function actualizar(int $id, array $data): array
    {
        $usuario = User::findOrFail($id);

        $rol = $data['rol'] ?? null;
        unset($data['rol']);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $usuario->update($data);

        if ($rol) {
            $usuario->syncRoles([$rol]);
        }

        return $usuario->load('roles')->toArray();
    }

    public function eliminar(int $id): void
    {
        User::findOrFail($id)->delete();
    }
}
