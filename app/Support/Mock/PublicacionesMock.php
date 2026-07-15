<?php

namespace App\Support\Mock;

class PublicacionesMock
{
    private const SESSION_KEY = 'mock_publicaciones';

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

    public static function actualizarEstado(int $id, string $estado): void
    {
        $items = collect(self::all())->map(function ($item) use ($id, $estado) {
            if ($item['id'] === $id) {
                $item['estado'] = $estado;
            }
            return $item;
        })->values()->toArray();

        session([self::SESSION_KEY => $items]);
    }


    private static function datosIniciales(): array
    {
        return [
            ['id' => 1, 'producto' => 'Huipil de Gala', 'codigo' => 'ART-2930', 'categoria' => 'Textiles', 'artesano' => 'María Sánchez', 'tienda' => 'Tienda Oaxaca Centro', 'estado' => 'PENDIENTE', 'descripcion' => 'Huipil bordado a mano con hilo de seda, elaborado por artesanas de San Antonino.', 'calificacion_promedio' => 4.3, 'imagen' => 'https://placehold.co/400x400/F8F5F2/D81B60?text=Huipil'],
            ['id' => 2, 'producto' => 'Jarrón de Barro Negro', 'codigo' => 'ART-2931', 'categoria' => 'Cerámica', 'artesano' => 'Pedro López', 'tienda' => 'Alfarería Coyotepec', 'estado' => 'SUSPENDIDO', 'descripcion' => 'Jarrón de barro negro pulido a mano, técnica tradicional de San Bartolo Coyotepec.', 'calificacion_promedio' => 5.0, 'imagen' => 'https://placehold.co/400x400/2B2B2B/FFFFFF?text=Jarron'],
            ['id' => 3, 'producto' => 'Alebrije Jaguar', 'codigo' => 'ART-2935', 'categoria' => 'Talla en madera', 'artesano' => 'Juana Ruiz', 'tienda' => 'Tilcajete Artesanal', 'estado' => 'PENDIENTE', 'descripcion' => 'Talla de copal pintada a mano representando un jaguar, arte tradicional de San Martín Tilcajete.', 'calificacion_promedio' => 3.8, 'imagen' => 'https://placehold.co/400x400/E65C00/FFFFFF?text=Alebrije'],
            ['id' => 4, 'producto' => 'Rebozo de Seda', 'codigo' => 'ART-2936', 'categoria' => 'Textiles', 'artesano' => 'Elena Cruz', 'tienda' => 'Textiles del Valle', 'estado' => 'REVISADO', 'descripcion' => 'Rebozo tejido en telar de cintura con seda teñida naturalmente.', 'calificacion_promedio' => 4.9, 'imagen' => 'https://placehold.co/400x400/D81B60/FFFFFF?text=Rebozo'],
            ['id' => 5, 'producto' => 'Collar de Plata', 'codigo' => 'ART-2937', 'categoria' => 'Joyería', 'artesano' => 'Marcos Díaz', 'tienda' => 'Platería Mixteca', 'estado' => 'PENDIENTE', 'descripcion' => 'Collar de plata .925 trabajado a mano con técnica de filigrana oaxaqueña.', 'calificacion_promedio' => 3.2, 'imagen' => 'https://placehold.co/400x400/4338CA/FFFFFF?text=Collar'],
            ['id' => 6, 'producto' => 'Vasija Trenzada', 'codigo' => 'ART-2938', 'categoria' => 'Cerámica', 'artesano' => 'Rosa Jiménez', 'tienda' => 'Alfarería Atzompa', 'estado' => 'ELIMINADO', 'descripcion' => 'Vasija de barro con diseño trenzado, hecha a mano en Atzompa.', 'calificacion_promedio' => 2.1, 'imagen' => 'https://placehold.co/400x400/990000/FFFFFF?text=Vasija'],
            ['id' => 7, 'producto' => 'Máscara Ceremonial', 'codigo' => 'ART-2939', 'categoria' => 'Talla en madera', 'artesano' => 'Luis Torres', 'tienda' => 'Tilcajete Artesanal', 'estado' => 'SUSPENDIDO', 'descripcion' => 'Máscara ceremonial tallada a mano, réplica de piezas usadas en festividades tradicionales.', 'calificacion_promedio' => 4.0, 'imagen' => 'https://placehold.co/400x400/008080/FFFFFF?text=Mascara'],
            ['id' => 8, 'producto' => 'Blusa Bordada', 'codigo' => 'ART-2940', 'categoria' => 'Textiles', 'artesano' => 'Carmen Vidal', 'tienda' => 'Textiles del Valle', 'estado' => 'REVISADO', 'descripcion' => 'Blusa de manta con bordado floral hecho a mano, punto de cruz tradicional.', 'calificacion_promedio' => 4.7, 'imagen' => 'https://placehold.co/400x400/D81B60/FFFFFF?text=Blusa'],
            ['id' => 9, 'producto' => 'Arete Filigrana', 'codigo' => 'ART-2941', 'categoria' => 'Joyería', 'artesano' => 'Ana Morales', 'tienda' => 'Platería Mixteca', 'estado' => 'PENDIENTE', 'descripcion' => 'Aretes de filigrana en plata, técnica heredada de generaciones de joyeros oaxaqueños.', 'calificacion_promedio' => 4.1, 'imagen' => 'https://placehold.co/400x400/EAB308/FFFFFF?text=Arete'],
            ['id' => 10, 'producto' => 'Plato Decorativo', 'codigo' => 'ART-2942', 'categoria' => 'Cerámica', 'artesano' => 'Jorge Ramos', 'tienda' => 'Alfarería Coyotepec', 'estado' => 'REVISADO', 'descripcion' => 'Plato decorativo de talavera pintado a mano con motivos florales.', 'calificacion_promedio' => 4.6, 'imagen' => 'https://placehold.co/400x400/2E8B57/FFFFFF?text=Plato'],
            ['id' => 11, 'producto' => 'Sombrero de Palma', 'codigo' => 'ART-2943', 'categoria' => 'Textiles', 'artesano' => 'Sofía Herrera', 'tienda' => 'Textiles del Valle', 'estado' => 'PENDIENTE', 'descripcion' => 'Sombrero tejido a mano con palma seca, técnica artesanal de la Mixteca.', 'calificacion_promedio' => 3.5, 'imagen' => 'https://placehold.co/400x400/E65C00/FFFFFF?text=Sombrero'],
            ['id' => 12, 'producto' => 'Anillo Grabado', 'codigo' => 'ART-2944', 'categoria' => 'Joyería', 'artesano' => 'Diego Salas', 'tienda' => 'Platería Mixteca', 'estado' => 'ELIMINADO', 'descripcion' => 'Anillo de plata con grabado a mano de iconografía zapoteca.', 'calificacion_promedio' => 2.0, 'imagen' => 'https://placehold.co/400x400/990000/FFFFFF?text=Anillo'],
        ];
    }
}
