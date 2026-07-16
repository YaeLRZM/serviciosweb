<?php

namespace App\Services\Usuarios;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UsuariosDataService
{
    /**
     * Listado real: users (schema actual) + roles Spatie.
     *
     * Columnas users: nombre, apellido_paterno, apellido_materno, email, password, timestamps.
     * Sin columnas name ni estatus.
     */
    public function listar(array $filtros = []): array
    {
        $query = User::query()->with('roles');

        if (! empty($filtros['rol'])) {
            // `name` aquí es roles.name (Spatie), no users.name
            $query->whereHas('roles', fn ($q) => $q->where('name', $filtros['rol']));
        }

        if (! empty($filtros['busqueda'])) {
            $busqueda = $filtros['busqueda'];
            $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

            $query->where(function ($q) use ($busqueda, $like) {
                $q->where('nombre', $like, "%{$busqueda}%")
                    ->orWhere('apellido_paterno', $like, "%{$busqueda}%")
                    ->orWhere('apellido_materno', $like, "%{$busqueda}%")
                    ->orWhere('email', $like, "%{$busqueda}%");
            });
        }

        return $query->latest()
            ->get()
            ->map(fn (User $user) => $this->mapearParaVista($user))
            ->all();
    }

    public function crear(array $data): array
    {
        $rol = $data['rol'] ?? 'user';
        unset($data['rol']);

        $data = $this->normalizarCamposEscritura($data);

        $usuario = new User;
        $usuario->forceFill($data)->save();
        $usuario->assignRole($rol);

        return $this->mapearParaVista($usuario->load('roles'));
    }

    public function find(int $id): ?array
    {
        $usuario = User::query()->with('roles')->find($id);

        return $usuario ? $this->mapearParaVista($usuario) : null;
    }

    public function actualizar(int $id, array $data): array
    {
        $usuario = User::findOrFail($id);

        $rol = $data['rol'] ?? null;
        unset($data['rol']);

        $data = $this->normalizarCamposEscritura($data);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $usuario->forceFill($data)->save();

        if ($rol) {
            $usuario->syncRoles([$rol]);
        }

        return $this->mapearParaVista($usuario->load('roles'));
    }

    public function eliminar(int $id): void
    {
        User::findOrFail($id)->delete();
    }

    /**
     * @return array{
     *   id:int,
     *   nombre:string,
     *   nombre_raw:?string,
     *   apellido_paterno:?string,
     *   apellido_materno:?string,
     *   email:string,
     *   rol:?string,
     *   created_at:?string
     * }
     */
    protected function mapearParaVista(User $user): array
    {
        $rol = $user->roles->first()?->name
            ?? $user->getRoleNames()->first();

        return [
            'id' => (int) $user->id,
            // Nombre completo centralizado en User::nombre_completo
            'nombre' => $user->nombre_completo,
            'nombre_raw' => filled($user->nombre) ? (string) $user->nombre : null,
            'apellido_paterno' => filled($user->apellido_paterno) ? (string) $user->apellido_paterno : null,
            'apellido_materno' => filled($user->apellido_materno) ? (string) $user->apellido_materno : null,
            'email' => (string) $user->email,
            'rol' => $rol,
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }

    /**
     * Solo columnas reales de escritura en users.
     */
    protected function normalizarCamposEscritura(array $data): array
    {
        if (array_key_exists('apellido_materno', $data) && $data['apellido_materno'] === '') {
            $data['apellido_materno'] = null;
        }

        return array_intersect_key($data, array_flip([
            'nombre',
            'apellido_paterno',
            'apellido_materno',
            'email',
            'password',
            'email_verified_at',
        ]));
    }
}
