<?php

namespace App\Services\Categorias;

use App\Models\Categoria;
use App\Support\Mock\CategoriasMock;

class CategoriasDataService
{
    protected function usarMock(): bool
    {
        return (bool) config('features.mock_categorias', true);
    }

    public function listar(): array
    {
        if ($this->usarMock()) {
            return CategoriasMock::all();
        }

        return Categoria::query()->orderBy('nombre')->get()->toArray();
    }

    public function find(int $id): ?array
    {
        if ($this->usarMock()) {
            return CategoriasMock::find($id);
        }

        return Categoria::find($id)?->toArray();
    }

    public function crear(array $data): array
    {
        if ($this->usarMock()) {
            return CategoriasMock::crear($data);
        }

        return Categoria::create($data)->toArray();
    }

    /**
     * @throws \RuntimeException si la categoría no existe
     */
    public function actualizar(int $id, array $data): array
    {
        if ($this->usarMock()) {
            return CategoriasMock::actualizar($id, $data);
        }

        $categoria = Categoria::findOrFail($id);
        $categoria->update($data);

        return $categoria->toArray();
    }

    public function alternarVisibilidad(int $id): void
    {
        if ($this->usarMock()) {
            CategoriasMock::alternarVisibilidad($id);

            return;
        }

        $categoria = Categoria::findOrFail($id);
        $categoria->update(['visible' => ! $categoria->visible]);
    }

    public function eliminar(int $id): void
    {
        if ($this->usarMock()) {
            CategoriasMock::eliminar($id);

            return;
        }

        Categoria::findOrFail($id)->delete();
    }
}
