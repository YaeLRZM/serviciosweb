<?php

namespace App\Support\Mock;

class CategoriasMock
{
    private const SESSION_KEY = 'mock_categorias';

    public static function all(): array
    {
        if (! session()->has(self::SESSION_KEY)) {
            session([self::SESSION_KEY => self::datosIniciales()]);
        }

        return session(self::SESSION_KEY);
    }

    public static function find(int $id): ?array
    {
        return collect(self::all())->firstWhere('id', $id);
    }

    public static function crear(array $data): array
    {
        $items = collect(self::all());

        $nueva = [
            'id' => $items->max('id') + 1,
            'nombre' => $data['nombre'] ?? '',
            'descripcion' => $data['descripcion'] ?? null,
            'imagen' => $data['imagen'] ?? null,
            'visible' => (bool) ($data['visible'] ?? true),
            'created_at' => now()->toIso8601String(),
        ];

        session([self::SESSION_KEY => $items->push($nueva)->values()->all()]);

        return $nueva;
    }

    public static function actualizar(int $id, array $data): array
    {
        $actualizada = null;

        $items = collect(self::all())->map(function ($item) use ($id, $data, &$actualizada) {
            if ((int) $item['id'] !== $id) {
                return $item;
            }

            $item = array_merge($item, [
                'nombre' => $data['nombre'] ?? $item['nombre'],
                'descripcion' => $data['descripcion'] ?? $item['descripcion'],
                'imagen' => $data['imagen'] ?? $item['imagen'],
                'visible' => array_key_exists('visible', $data) ? (bool) $data['visible'] : $item['visible'],
            ]);

            $actualizada = $item;

            return $item;
        })->values()->all();

        session([self::SESSION_KEY => $items]);

        return $actualizada ?? throw new \RuntimeException('Categoría no encontrada.');
    }

    public static function alternarVisibilidad(int $id): void
    {
        $items = collect(self::all())->map(function ($item) use ($id) {
            if ((int) $item['id'] === $id) {
                $item['visible'] = ! $item['visible'];
            }

            return $item;
        })->values()->toArray();

        session([self::SESSION_KEY => $items]);
    }

    public static function eliminar(int $id): void
    {
        $items = collect(self::all())
            ->reject(fn ($item) => (int) $item['id'] === $id)
            ->values()
            ->all();

        session([self::SESSION_KEY => $items]);
    }

    /**
     * Forma de cada item (contrato UI / mismas columnas que el modelo Categoria):
     * id, nombre, descripcion, imagen, visible, created_at
     */
    private static function datosIniciales(): array
    {
        return [
            [
                'id' => 1,
                'nombre' => 'Textiles',
                'descripcion' => 'Huipiles, rebozos y prendas tejidas a mano en telar de cintura.',
                'imagen' => 'https://images.unsplash.com/photo-1528812969535-4bcefa07a1c2?w=500',
                'visible' => true,
                'created_at' => now()->subMonths(6)->toIso8601String(),
            ],
            [
                'id' => 2,
                'nombre' => 'Cerámica',
                'descripcion' => 'Barro negro, talavera y piezas de alfarería tradicional oaxaqueña.',
                'imagen' => 'https://images.unsplash.com/photo-1565193566173-7a0ee3dbe261?w=500',
                'visible' => true,
                'created_at' => now()->subMonths(4)->toIso8601String(),
            ],
            [
                'id' => 3,
                'nombre' => 'Talla en madera',
                'descripcion' => 'Alebrijes y figuras talladas y pintadas a mano.',
                'imagen' => 'https://images.unsplash.com/photo-1544829099-b9a0c07fad1a?w=500',
                'visible' => true,
                'created_at' => now()->subDays(20)->toIso8601String(),
            ],
            [
                'id' => 4,
                'nombre' => 'Joyería',
                'descripcion' => 'Filigrana en plata y piezas de orfebrería artesanal.',
                'imagen' => 'https://images.unsplash.com/photo-1611085583191-a3b181a88401?w=500',
                'visible' => false,
                'created_at' => now()->subDays(3)->toIso8601String(),
            ],
            [
                'id' => 5,
                'nombre' => 'Cuero',
                'descripcion' => 'Talabartería y accesorios de piel trabajados a mano.',
                'imagen' => 'https://images.unsplash.com/photo-1523293182086-7651a899d37f?w=500',
                'visible' => true,
                'created_at' => now()->subYear()->toIso8601String(),
            ],
            [
                'id' => 6,
                'nombre' => 'Cestería',
                'descripcion' => 'Canastas y piezas tejidas con palma, carrizo y vara de sauce.',
                'imagen' => 'https://images.unsplash.com/photo-1622560481156-01f75db6b046?w=500',
                'visible' => true,
                'created_at' => now()->subMonths(9)->toIso8601String(),
            ],
            [
                'id' => 7,
                'nombre' => 'Vidrio soplado',
                'descripcion' => 'Piezas decorativas y utilitarias trabajadas al calor del horno.',
                'imagen' => 'https://images.unsplash.com/photo-1528697203043-733ac9990fc6?w=500',
                'visible' => true,
                'created_at' => now()->subDays(50)->toIso8601String(),
            ],
            [
                'id' => 8,
                'nombre' => 'Instrumentos musicales',
                'descripcion' => 'Guitarras, jaranas y percusiones hechas a mano por luthiers artesanales.',
                'imagen' => 'https://images.unsplash.com/photo-1511379938547-c1f69419868d?w=500',
                'visible' => true,
                'created_at' => now()->subMonths(2)->toIso8601String(),
            ],
            [
                'id' => 9,
                'nombre' => 'Velas y cerería',
                'descripcion' => 'Velas decorativas de cera de abeja, típicas de festividades religiosas.',
                'imagen' => 'https://images.unsplash.com/photo-1602523961358-f9f03dd557db?w=500',
                'visible' => false,
                'created_at' => now()->subDays(15)->toIso8601String(),
            ],
            [
                'id' => 10,
                'nombre' => 'Mezcal y destilados',
                'descripcion' => 'Mezcal artesanal producido en palenques familiares tradicionales.',
                'imagen' => 'https://images.unsplash.com/photo-1569529465841-dfecdab7503b?w=500',
                'visible' => true,
                'created_at' => now()->subDays(8)->toIso8601String(),
            ],
            [
                'id' => 11,
                'nombre' => 'Papel amate',
                'descripcion' => 'Papel hecho a mano con corteza de árbol y pinturas naturales.',
                'imagen' => 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?w=500',
                'visible' => true,
                'created_at' => now()->subDays(2)->toIso8601String(),
            ],
            [
                'id' => 12,
                'nombre' => 'Talavera poblana',
                'descripcion' => 'Cerámica esmaltada con denominación de origen de Puebla.',
                'imagen' => 'https://images.unsplash.com/photo-1610701596007-11502861dcfa?w=500',
                'visible' => true,
                'created_at' => now()->subDays(33)->toIso8601String(),
            ],
            [
                'id' => 13,
                'nombre' => 'Piñatería',
                'descripcion' => 'Piñatas de cartón y papel picado para fiestas tradicionales.',
                'imagen' => 'https://images.unsplash.com/photo-1533230050171-f9e33dd9764a?w=500',
                'visible' => true,
                'created_at' => now()->subDays(11)->toIso8601String(),
            ],
            [
                'id' => 14,
                'nombre' => 'Hojalatería',
                'descripcion' => 'Espejos, marcos y adornos trabajados en lámina de hojalata.',
                'imagen' => 'https://images.unsplash.com/photo-1620207418302-439b387441b0?w=500',
                'visible' => true,
                'created_at' => now()->subMonths(5)->toIso8601String(),
            ],
            [
                'id' => 15,
                'nombre' => 'Juguetería tradicional',
                'descripcion' => 'Trompos, baleros y muñecas de trapo hechos a mano.',
                'imagen' => 'https://images.unsplash.com/photo-1558877385-81a1c7e67d72?w=500',
                'visible' => false,
                'created_at' => now()->subDays(70)->toIso8601String(),
            ],
            [
                'id' => 16,
                'nombre' => 'Máscaras ceremoniales',
                'descripcion' => 'Máscaras talladas y pintadas usadas en danzas y festividades.',
                'imagen' => 'https://images.unsplash.com/photo-1604882737292-3f2a8d24a9a5?w=500',
                'visible' => true,
                'created_at' => now()->subDays(25)->toIso8601String(),
            ],
            [
                'id' => 17,
                'nombre' => 'Huaraches artesanales',
                'descripcion' => 'Calzado de piel y suela de llanta reciclada, tejido a mano.',
                'imagen' => 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?w=500',
                'visible' => true,
                'created_at' => now()->subDays(4)->toIso8601String(),
            ],
            [
                'id' => 18,
                'nombre' => 'Petates y tule',
                'descripcion' => 'Tapetes y objetos tejidos con fibra de tule de laguna.',
                'imagen' => 'https://images.unsplash.com/photo-1606722590583-6951b5ea92ad?w=500',
                'visible' => true,
                'created_at' => now()->subMonths(8)->toIso8601String(),
            ],
            [
                'id' => 19,
                'nombre' => 'Chocolate artesanal',
                'descripcion' => 'Tabletas y bebidas de cacao molido en piedra, receta tradicional.',
                'imagen' => 'https://images.unsplash.com/photo-1511381939415-e44015466834?w=500',
                'visible' => true,
                'created_at' => now()->subDays(17)->toIso8601String(),
            ],
            [
                'id' => 20,
                'nombre' => 'Dulces tradicionales',
                'descripcion' => 'Dulces de leche, camote y frutas cristalizadas de recetas regionales.',
                'imagen' => 'https://images.unsplash.com/photo-1571506165871-ee72a35bc9d4?w=500',
                'visible' => true,
                'created_at' => now()->subDays(60)->toIso8601String(),
            ],
            [
                'id' => 21,
                'nombre' => 'Arte en popote',
                'descripcion' => 'Figuras y cuadros decorativos elaborados con popote de trigo.',
                'imagen' => 'https://images.unsplash.com/photo-1598899246544-cc9c0be8f2a9?w=500',
                'visible' => false,
                'created_at' => now()->subMonths(3)->toIso8601String(),
            ],
            [
                'id' => 22,
                'nombre' => 'Sombrerería',
                'descripcion' => 'Sombreros de palma y fieltro tejidos a mano.',
                'imagen' => 'https://images.unsplash.com/photo-1521369909029-2afed882baee?w=500',
                'visible' => true,
                'created_at' => now()->subDays(38)->toIso8601String(),
            ],
            [
                'id' => 23,
                'nombre' => 'Bordado indígena',
                'descripcion' => 'Prendas bordadas a mano con simbología de comunidades originarias.',
                'imagen' => 'https://images.unsplash.com/photo-1541199249251-f713e6145474?w=500',
                'visible' => true,
                'created_at' => now()->subDays(6)->toIso8601String(),
            ],
        ];
    }
}
