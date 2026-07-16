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
            ['id' => 13, 'producto' => 'Plato de Talavera', 'codigo' => 'ART-2945', 'categoria' => 'Talavera poblana', 'artesano' => 'Herminia Bautista', 'tienda' => 'Talavera de Puebla', 'estado' => 'PENDIENTE', 'descripcion' => 'Plato de talavera esmaltada pintado a mano con motivos florales.', 'calificacion_promedio' => 4.5, 'imagen' => 'https://placehold.co/400x400/1E40AF/FFFFFF?text=Talavera'],
            ['id' => 14, 'producto' => 'Piñata de Siete Picos', 'codigo' => 'ART-2946', 'categoria' => 'Piñatería', 'artesano' => 'Salvador Ibarra', 'tienda' => 'Piñatas Acolman', 'estado' => 'REVISADO', 'descripcion' => 'Piñata tradicional de cartón y papel china, hecha a mano.', 'calificacion_promedio' => 4.2, 'imagen' => 'https://placehold.co/400x400/DC2626/FFFFFF?text=Pinata'],
            ['id' => 15, 'producto' => 'Espejo de Hojalata', 'codigo' => 'ART-2947', 'categoria' => 'Hojalatería', 'artesano' => 'Concepción Farías', 'tienda' => 'Hojalatería San Miguel', 'estado' => 'PENDIENTE', 'descripcion' => 'Espejo decorativo con marco de hojalata repujada a mano.', 'calificacion_promedio' => 3.9, 'imagen' => 'https://placehold.co/400x400/71717A/FFFFFF?text=Hojalata'],
            ['id' => 16, 'producto' => 'Trompo de Madera', 'codigo' => 'ART-2948', 'categoria' => 'Juguetería tradicional', 'artesano' => 'Efraín Domínguez', 'tienda' => 'Juguetería Chignahuapan', 'estado' => 'SUSPENDIDO', 'descripcion' => 'Trompo de madera torneada pintado a mano, juguete tradicional.', 'calificacion_promedio' => 3.4, 'imagen' => 'https://placehold.co/400x400/CA8A04/FFFFFF?text=Trompo'],
            ['id' => 17, 'producto' => 'Máscara de Viejito', 'codigo' => 'ART-2949', 'categoria' => 'Máscaras ceremoniales', 'artesano' => 'Refugio Martínez', 'tienda' => 'Máscaras Tocuaro', 'estado' => 'PENDIENTE', 'descripcion' => 'Máscara tallada en madera de tilo, usada en la danza de los viejitos.', 'calificacion_promedio' => 4.8, 'imagen' => 'https://placehold.co/400x400/78350F/FFFFFF?text=Mascara'],
            ['id' => 18, 'producto' => 'Huaraches de Piel', 'codigo' => 'ART-2950', 'categoria' => 'Huaraches artesanales', 'artesano' => 'Bernardo Solano', 'tienda' => 'Huaraches Sahuayo', 'estado' => 'REVISADO', 'descripcion' => 'Huaraches tejidos a mano en piel curtida con suela reciclada.', 'calificacion_promedio' => 4.6, 'imagen' => 'https://placehold.co/400x400/92400E/FFFFFF?text=Huarache'],
            ['id' => 19, 'producto' => 'Petate de Tule', 'codigo' => 'ART-2951', 'categoria' => 'Petates y tule', 'artesano' => 'Petrona Jiménez', 'tienda' => 'Tule y Fibras', 'estado' => 'ELIMINADO', 'descripcion' => 'Petate tejido a mano con fibra de tule de laguna.', 'calificacion_promedio' => 2.5, 'imagen' => 'https://placehold.co/400x400/4D7C0F/FFFFFF?text=Petate'],
            ['id' => 20, 'producto' => 'Tableta de Chocolate', 'codigo' => 'ART-2952', 'categoria' => 'Chocolate artesanal', 'artesano' => 'Anselmo Vargas', 'tienda' => 'Chocolate Villahermosa', 'estado' => 'PENDIENTE', 'descripcion' => 'Chocolate de mesa molido en piedra con canela y almendra.', 'calificacion_promedio' => 4.7, 'imagen' => 'https://placehold.co/400x400/451A03/FFFFFF?text=Chocolate'],
            ['id' => 21, 'producto' => 'Camote Cristalizado', 'codigo' => 'ART-2953', 'categoria' => 'Dulces tradicionales', 'artesano' => 'Guadalupe Zárate', 'tienda' => 'Dulces de Puebla', 'estado' => 'REVISADO', 'descripcion' => 'Dulce de camote cristalizado, receta artesanal poblana.', 'calificacion_promedio' => 4.3, 'imagen' => 'https://placehold.co/400x400/EA580C/FFFFFF?text=Camote'],
            ['id' => 22, 'producto' => 'Figura de Popote', 'codigo' => 'ART-2954', 'categoria' => 'Arte en popote', 'artesano' => 'Higinio Cortés', 'tienda' => 'Arte en Popote Tzintzuntzan', 'estado' => 'PENDIENTE', 'descripcion' => 'Cuadro decorativo elaborado con popote de trigo teñido a mano.', 'calificacion_promedio' => 3.7, 'imagen' => 'https://placehold.co/400x400/A16207/FFFFFF?text=Popote'],
            ['id' => 23, 'producto' => 'Sombrero de Palma Bécal', 'codigo' => 'ART-2955', 'categoria' => 'Sombrerería', 'artesano' => 'Dolores Mendoza', 'tienda' => 'Sombrerería Bécal', 'estado' => 'REVISADO', 'descripcion' => 'Sombrero fino de palma tejido bajo tierra, técnica de Bécal.', 'calificacion_promedio' => 4.9, 'imagen' => 'https://placehold.co/400x400/CA8A04/FFFFFF?text=Sombrero'],
            ['id' => 24, 'producto' => 'Huipil Bordado Amuzgo', 'codigo' => 'ART-2956', 'categoria' => 'Bordado indígena', 'artesano' => 'Amalia Ríos', 'tienda' => 'Bordados Amuzgos', 'estado' => 'PENDIENTE', 'descripcion' => 'Huipil de gala bordado a mano con simbología amuzga tradicional.', 'calificacion_promedio' => 4.9, 'imagen' => 'https://placehold.co/400x400/D81B60/FFFFFF?text=Huipil'],
        ];
    }
}
