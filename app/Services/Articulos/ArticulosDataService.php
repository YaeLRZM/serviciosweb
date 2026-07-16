<?php

namespace App\Services\Articulos;

use App\Models\Articulo;
use App\Models\Inventario;

/**
 * Lectura/escritura local de artículos para admin (sin self-HTTP).
 * Schema real pgsql (articulos): nombre, talla, color, bordado, tela, region + FKs.
 * Stock vive en inventarios.stock_actual (no hay precio/descripcion/stock en articulos).
 */
class ArticulosDataService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function all(): array
    {
        return Articulo::query()
            ->with(['categoria', 'artesano', 'tienda', 'inventario'])
            ->latest()
            ->get()
            ->map(fn (Articulo $a) => $this->mapear($a))
            ->all();
    }

    public function find(int $id): ?array
    {
        $articulo = Articulo::query()
            ->with(['categoria', 'artesano', 'tienda', 'inventario'])
            ->find($id);

        return $articulo ? $this->mapear($articulo) : null;
    }

    /**
     * @return array{total:int, en_stock:int, agotados:int}
     */
    public function stats(): array
    {
        $total = Articulo::query()->count();

        $enStock = Articulo::query()
            ->whereHas('inventario', fn ($q) => $q->where('stock_actual', '>', 0))
            ->count();

        return [
            'total' => $total,
            'en_stock' => $enStock,
            'agotados' => max(0, $total - $enStock),
        ];
    }

    /**
     * Actualiza solo columnas reales de articulos + stock en inventarios.
     *
     * @param  array{nombre?:string, talla?:string, color?:string, bordado?:string, tela?:string, region?:string, stock?:int}  $data
     */
    public function actualizar(int $id, array $data): array
    {
        $articulo = Articulo::query()->findOrFail($id);

        $campos = array_intersect_key($data, array_flip([
            'nombre', 'talla', 'color', 'bordado', 'tela', 'region',
        ]));

        if ($campos !== []) {
            $articulo->update($campos);
        }

        if (array_key_exists('stock', $data)) {
            $inv = Inventario::query()->firstOrNew(['articulo_id' => $articulo->id]);
            $inv->stock_actual = (int) $data['stock'];
            if (! $inv->exists) {
                $inv->stock_minimo = 0;
            }
            $inv->save();
        }

        return $this->find($articulo->id) ?? [];
    }

    public function eliminar(int $id): void
    {
        Articulo::query()->findOrFail($id)->delete();
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapear(Articulo $a): array
    {
        $stock = (int) ($a->inventario?->stock_actual ?? 0);

        return [
            'id' => (int) $a->id,
            'nombre' => (string) $a->nombre,
            'talla' => (string) ($a->talla ?? ''),
            'color' => (string) ($a->color ?? ''),
            'bordado' => (string) ($a->bordado ?? ''),
            'tela' => (string) ($a->tela ?? ''),
            'region' => (string) ($a->region ?? ''),
            'stock' => $stock,
            'categoria' => [
                'id' => $a->categoria?->id,
                'nombre' => $a->categoria?->nombre ?? '—',
            ],
            'artesano' => [
                'id' => $a->artesano?->id,
                'nombre' => $a->artesano?->nombre ?? '—',
            ],
            'tienda' => [
                'id' => $a->tienda?->id,
                'nombre' => $a->tienda?->nombre ?? '—',
            ],
        ];
    }
}
