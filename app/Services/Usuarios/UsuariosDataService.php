<?php

namespace App\Services\Usuarios;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UsuariosDataService
{
    /**
     * Listado real de users + roles (Spatie), con filtros opcionales.
     * Devuelve arrays listos para la vista admin (sin mocks).
     */
    public function listar(array $filtros = []): array
    {
        $query = User::query()->with('roles');

        if (! empty($filtros['rol'])) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $filtros['rol']));
        }

        // Columna opcional: solo filtrar si existe en el schema actual.
        if (! empty($filtros['estatus']) && Schema::hasColumn('users', 'estatus')) {
            $query->where('estatus', $filtros['estatus']);
        }

        if (! empty($filtros['busqueda'])) {
            $busqueda = $filtros['busqueda'];
            // PostgreSQL: ILIKE = LIKE case-insensitive. SQLite/MySQL fallback: LIKE (sqlite LIKE ya es CI).
            $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

            $query->where(function ($q) use ($busqueda, $like) {
                // Schema Laravel clásico: name
                if (Schema::hasColumn('users', 'name')) {
                    $q->where('name', $like, "%{$busqueda}%");
                }
                // Schema extendido: nombre + apellidos
                if (Schema::hasColumn('users', 'nombre')) {
                    $q->orWhere('nombre', $like, "%{$busqueda}%");
                }
                if (Schema::hasColumn('users', 'apellido_paterno')) {
                    $q->orWhere('apellido_paterno', $like, "%{$busqueda}%");
                }
                if (Schema::hasColumn('users', 'apellido_materno')) {
                    $q->orWhere('apellido_materno', $like, "%{$busqueda}%");
                }
                $q->orWhere('email', $like, "%{$busqueda}%");
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

        // forceFill: el fillable del modelo puede no listar "name" o "nombre" según rama/schema.
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

    /**
     * @throws \RuntimeException si la actualización falla
     */
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
     * Normaliza un User de BD a la forma que usa la vista admin.
     * Compatible con schema `name` (pgsql clásico) o `nombre`+apellidos.
     */
    protected function mapearParaVista(User $user): array
    {
        $partes = [];

        if (Schema::hasColumn('users', 'nombre') || filled($user->nombre ?? null)) {
            $partes = array_filter([
                $user->nombre ?? null,
                $user->apellido_paterno ?? null,
                $user->apellido_materno ?? null,
            ], fn ($v) => filled($v));
        }

        $nombreCompleto = trim(implode(' ', $partes));

        // Fallback schema clásico Laravel: columna `name`
        if ($nombreCompleto === '' && filled($user->name ?? null)) {
            $nombreCompleto = (string) $user->name;
        }

        if ($nombreCompleto === '') {
            $nombreCompleto = (string) $user->email;
        }

        // Rol real Spatie (eager-loaded). Null si no tiene roles.
        $rol = $user->roles->first()?->name
            ?? $user->getRoleNames()->first();

        // Convención mínima si no hay columna/valor estatus.
        $estatus = Schema::hasColumn('users', 'estatus')
            ? (string) ($user->estatus ?: 'activo')
            : 'activo';

        return [
            'id' => (int) $user->id,
            'nombre' => $nombreCompleto,
            'nombre_raw' => $user->nombre ?? $user->name ?? null,
            'apellido_paterno' => $user->apellido_paterno ?? null,
            'apellido_materno' => $user->apellido_materno ?? null,
            'email' => (string) $user->email,
            'rol' => $rol,
            'estatus' => $estatus,
            'created_at' => $user->created_at?->toIso8601String(),
            // Alias defensivo
            'name' => $nombreCompleto,
        ];
    }

    /**
     * Ajusta payload de escritura al schema real (name vs nombre).
     */
    protected function normalizarCamposEscritura(array $data): array
    {
        $valorNombre = $data['nombre'] ?? $data['name'] ?? null;

        if (Schema::hasColumn('users', 'name')) {
            if ($valorNombre !== null) {
                $data['name'] = $valorNombre;
            }
            unset($data['nombre'], $data['apellido_paterno'], $data['apellido_materno']);
        } elseif (Schema::hasColumn('users', 'nombre')) {
            if ($valorNombre !== null) {
                $data['nombre'] = $valorNombre;
            }
            unset($data['name']);
        }

        // No escribir columnas que no existen en el schema runtime (pgsql actual sin estatus).
        if (array_key_exists('estatus', $data) && ! Schema::hasColumn('users', 'estatus')) {
            unset($data['estatus']);
        }

        return $data;
    }
}
