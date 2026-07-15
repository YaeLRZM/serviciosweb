<?php

namespace App\Services\Categorias;

use App\Models\Categoria;

class CategoriasDataService
{
    public function listar(): array
    {
        return Categoria::query()->orderBy('nombre')->get()->toArray();
    }

    public function find(int $id): ?array
    {
        return Categoria::find($id)?->toArray();
    }

    public function crear(array $data): array
    {
        return Categoria::create($data)->toArray();
    }

    /**
     * @throws \RuntimeException si la categoría no existe
     */
    public function actualizar(int $id, array $data): array
    {
        $categoria = Categoria::findOrFail($id);
        $categoria->update($data);

        return $categoria->toArray();
    }

    public function alternarVisibilidad(int $id): void
    {
        $categoria = Categoria::findOrFail($id);
        $categoria->update(['visible' => ! $categoria->visible]);
    }

    public function eliminar(int $id): void
    {
        Categoria::findOrFail($id)->delete();
    }
}
