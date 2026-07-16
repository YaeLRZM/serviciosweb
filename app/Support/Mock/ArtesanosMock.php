<?php

namespace App\Support\Mock;

class ArtesanosMock
{
    private const SESSION_KEY = 'mock_artesanos';

    private const ESTADOS_COLA = ['revision', 'nueva', 'documentos'];

    private const ESTADO_LABELS = [
        'revision' => 'En revisión',
        'nueva' => 'Nueva solicitud',
        'documentos' => 'Documentación pendiente',
    ];

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

    /**
     * Solicitudes pendientes de revisión (aún no aprobadas ni rechazadas).
     */
    public static function colaVerificacion(): array
    {
        return collect(self::all())
            ->filter(fn ($item) => in_array($item['estado'], self::ESTADOS_COLA, true))
            ->map(fn ($item) => [
                ...$item,
                'estadoLabel' => self::ESTADO_LABELS[$item['estado']] ?? $item['estado'],
                'accionLabel' => $item['estado'] === 'documentos' ? 'Contactar' : 'Ver detalle',
            ])
            ->values()
            ->all();
    }

    /**
     * Socios ya aprobados, ordenados por ventas totales.
     */
    public static function activos(): array
    {
        return collect(self::all())
            ->filter(fn ($item) => $item['estado'] === 'aprobado')
            ->map(fn ($item) => [...$item, 'verificado' => true])
            ->sortByDesc('ventas_total')
            ->values()
            ->all();
    }

    public static function guardarDictamen(int $id, string $dictamen, string $notas): void
    {
        $nuevoEstado = match ($dictamen) {
            'Aprobar' => 'aprobado',
            'Rechazar' => 'rechazado',
            default => 'documentos', // 'Solicitar información'
        };

        $items = collect(self::all())->map(function ($item) use ($id, $nuevoEstado, $notas) {
            if ((int) $item['id'] === $id) {
                $item['estado'] = $nuevoEstado;
                $item['notas_moderacion'] = $notas;
            }

            return $item;
        })->values()->all();

        session([self::SESSION_KEY => $items]);
    }

    public static function alternarDestacado(int $id): void
    {
        $items = collect(self::all())->map(function ($item) use ($id) {
            if ((int) $item['id'] === $id) {
                $item['destacado'] = ! $item['destacado'];
            }

            return $item;
        })->values()->all();

        session([self::SESSION_KEY => $items]);
    }

    /**
     * Forma de cada item:
     * id, nombre, especialidad, foto, ubicacion, estado, ventas_total,
     * ventas_items, rating, destacado, notas_moderacion
     */
    private static function datosIniciales(): array
    {
        return [
            [
                'id' => 1,
                'nombre' => 'Mateo Ruiz',
                'especialidad' => 'Talla de alebrijes',
                'foto' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200',
                'ubicacion' => 'San Martín Tilcajete, MX',
                'estado' => 'revision',
                'ventas_total' => 0,
                'ventas_items' => 0,
                'rating' => null,
                'destacado' => false,
                'notas_moderacion' => '',
            ],
            [
                'id' => 2,
                'nombre' => 'Isabel Gómez',
                'especialidad' => 'Bordado de San Antonino',
                'foto' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=200',
                'ubicacion' => 'San Antonino Castillo Velasco, MX',
                'estado' => 'nueva',
                'ventas_total' => 0,
                'ventas_items' => 0,
                'rating' => null,
                'destacado' => false,
                'notas_moderacion' => '',
            ],
            [
                'id' => 3,
                'nombre' => 'Pedro Sánchez',
                'especialidad' => 'Barro negro',
                'foto' => 'https://images.unsplash.com/photo-1622037022824-0c71d511ad60?w=200',
                'ubicacion' => 'San Bartolo Coyotepec, MX',
                'estado' => 'documentos',
                'ventas_total' => 0,
                'ventas_items' => 0,
                'rating' => null,
                'destacado' => false,
                'notas_moderacion' => '',
            ],
            [
                'id' => 4,
                'nombre' => 'Carmen Jiménez',
                'especialidad' => 'Bordado de San Antonino',
                'foto' => 'https://images.unsplash.com/photo-1531123897727-8f129e1688ce?w=200',
                'ubicacion' => 'Oaxaca City, MX',
                'estado' => 'aprobado',
                'ventas_total' => 12450.00,
                'ventas_items' => 142,
                'rating' => 4.9,
                'destacado' => true,
                'notas_moderacion' => 'Verificada en 2024.',
            ],
            [
                'id' => 5,
                'nombre' => 'Tomás Vásquez',
                'especialidad' => 'Tapete zapoteco',
                'foto' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=200',
                'ubicacion' => 'Teotitlán del Valle, MX',
                'estado' => 'aprobado',
                'ventas_total' => 8920.00,
                'ventas_items' => 68,
                'rating' => 4.8,
                'destacado' => false,
                'notas_moderacion' => '',
            ],
            [
                'id' => 6,
                'nombre' => 'Gloria Méndez',
                'especialidad' => 'Alebrijes pintados a mano',
                'foto' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=200',
                'ubicacion' => 'Arrazola, MX',
                'estado' => 'aprobado',
                'ventas_total' => 3150.00,
                'ventas_items' => 29,
                'rating' => 4.7,
                'destacado' => false,
                'notas_moderacion' => '',
            ],
            [
                'id' => 7,
                'nombre' => 'Rosalinda Cruz',
                'especialidad' => 'Cestería de palma',
                'foto' => 'https://images.unsplash.com/photo-1573497491208-6b1acb260507?w=200',
                'ubicacion' => 'Santa María Tlahuitoltepec, MX',
                'estado' => 'revision',
                'ventas_total' => 0,
                'ventas_items' => 0,
                'rating' => null,
                'destacado' => false,
                'notas_moderacion' => '',
            ],
            [
                'id' => 8,
                'nombre' => 'Alberto Reyes',
                'especialidad' => 'Vidrio soplado',
                'foto' => 'https://images.unsplash.com/photo-1601582589907-f92af5ed9db8?w=200',
                'ubicacion' => 'Oaxaca City, MX',
                'estado' => 'nueva',
                'ventas_total' => 0,
                'ventas_items' => 0,
                'rating' => null,
                'destacado' => false,
                'notas_moderacion' => '',
            ],
            [
                'id' => 9,
                'nombre' => 'Felipa Santos',
                'especialidad' => 'Textiles de telar de cintura',
                'foto' => 'https://images.unsplash.com/photo-1544943910-4c1dc44aab44?w=200',
                'ubicacion' => 'Santo Tomás Jalieza, MX',
                'estado' => 'documentos',
                'ventas_total' => 0,
                'ventas_items' => 0,
                'rating' => null,
                'destacado' => false,
                'notas_moderacion' => 'Falta comprobante de domicilio.',
            ],
            [
                'id' => 10,
                'nombre' => 'Genaro Luna',
                'especialidad' => 'Talabartería',
                'foto' => 'https://images.unsplash.com/photo-1520975954732-35dd22299614?w=200',
                'ubicacion' => 'Miahuatlán de Porfirio Díaz, MX',
                'estado' => 'rechazado',
                'ventas_total' => 0,
                'ventas_items' => 0,
                'rating' => null,
                'destacado' => false,
                'notas_moderacion' => 'Documentación de identidad ilegible, se solicitó reenvío sin respuesta.',
            ],
            [
                'id' => 11,
                'nombre' => 'Adriana Blas',
                'especialidad' => 'Joyería de filigrana',
                'foto' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=200',
                'ubicacion' => 'San Sebastián Río Hondo, MX',
                'estado' => 'aprobado',
                'ventas_total' => 15680.00,
                'ventas_items' => 96,
                'rating' => 5.0,
                'destacado' => true,
                'notas_moderacion' => 'Verificada en 2023, socia destacada por reseñas.',
            ],
            [
                'id' => 12,
                'nombre' => 'Cristóbal Aquino',
                'especialidad' => 'Instrumentos musicales de madera',
                'foto' => 'https://images.unsplash.com/photo-1558098329-a11cff621064?w=200',
                'ubicacion' => 'Paracho, MX',
                'estado' => 'aprobado',
                'ventas_total' => 6740.00,
                'ventas_items' => 41,
                'rating' => 4.6,
                'destacado' => false,
                'notas_moderacion' => '',
            ],
            [
                'id' => 13,
                'nombre' => 'Yolanda Pérez',
                'especialidad' => 'Cerería y velas artesanales',
                'foto' => 'https://images.unsplash.com/photo-1602523961404-a53612e4e2f6?w=200',
                'ubicacion' => 'Ocotlán de Morelos, MX',
                'estado' => 'aprobado',
                'ventas_total' => 2380.00,
                'ventas_items' => 34,
                'rating' => 4.4,
                'destacado' => false,
                'notas_moderacion' => '',
            ],
            [
                'id' => 14,
                'nombre' => 'Wendy Chávez',
                'especialidad' => 'Papel amate pintado',
                'foto' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=200',
                'ubicacion' => 'San Pablito Pahuatlán, MX',
                'estado' => 'nueva',
                'ventas_total' => 0,
                'ventas_items' => 0,
                'rating' => null,
                'destacado' => false,
                'notas_moderacion' => '',
            ],
            [
                'id' => 15,
                'nombre' => 'Norberto Silva',
                'especialidad' => 'Mezcal artesanal',
                'foto' => 'https://images.unsplash.com/photo-1569529465841-dfecdab7503b?w=200',
                'ubicacion' => 'Santiago Matatlán, MX',
                'estado' => 'aprobado',
                'ventas_total' => 21340.00,
                'ventas_items' => 187,
                'rating' => 4.9,
                'destacado' => true,
                'notas_moderacion' => '',
            ],
            [
                'id' => 16,
                'nombre' => 'Herminia Bautista',
                'especialidad' => 'Talavera poblana',
                'foto' => 'https://images.unsplash.com/photo-1610701596007-11502861dcfa?w=200',
                'ubicacion' => 'Puebla, MX',
                'estado' => 'aprobado',
                'ventas_total' => 9860.00,
                'ventas_items' => 77,
                'rating' => 4.8,
                'destacado' => false,
                'notas_moderacion' => '',
            ],
            [
                'id' => 17,
                'nombre' => 'Salvador Ibarra',
                'especialidad' => 'Piñatería',
                'foto' => 'https://images.unsplash.com/photo-1533230050171-f9e33dd9764a?w=200',
                'ubicacion' => 'Acolman, MX',
                'estado' => 'revision',
                'ventas_total' => 0,
                'ventas_items' => 0,
                'rating' => null,
                'destacado' => false,
                'notas_moderacion' => '',
            ],
            [
                'id' => 18,
                'nombre' => 'Concepción Farías',
                'especialidad' => 'Hojalatería',
                'foto' => 'https://images.unsplash.com/photo-1620207418302-439b387441b0?w=200',
                'ubicacion' => 'San Miguel de Allende, MX',
                'estado' => 'nueva',
                'ventas_total' => 0,
                'ventas_items' => 0,
                'rating' => null,
                'destacado' => false,
                'notas_moderacion' => '',
            ],
            [
                'id' => 19,
                'nombre' => 'Efraín Domínguez',
                'especialidad' => 'Juguetería tradicional',
                'foto' => 'https://images.unsplash.com/photo-1558877385-81a1c7e67d72?w=200',
                'ubicacion' => 'Chignahuapan, MX',
                'estado' => 'documentos',
                'ventas_total' => 0,
                'ventas_items' => 0,
                'rating' => null,
                'destacado' => false,
                'notas_moderacion' => 'Falta constancia de situación fiscal.',
            ],
            [
                'id' => 20,
                'nombre' => 'Refugio Martínez',
                'especialidad' => 'Máscaras ceremoniales',
                'foto' => 'https://images.unsplash.com/photo-1604882737292-3f2a8d24a9a5?w=200',
                'ubicacion' => 'Tocuaro, MX',
                'estado' => 'aprobado',
                'ventas_total' => 4920.00,
                'ventas_items' => 22,
                'rating' => 4.5,
                'destacado' => false,
                'notas_moderacion' => '',
            ],
            [
                'id' => 21,
                'nombre' => 'Bernardo Solano',
                'especialidad' => 'Huaraches artesanales',
                'foto' => 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?w=200',
                'ubicacion' => 'Sahuayo, MX',
                'estado' => 'aprobado',
                'ventas_total' => 7310.00,
                'ventas_items' => 58,
                'rating' => 4.6,
                'destacado' => false,
                'notas_moderacion' => '',
            ],
            [
                'id' => 22,
                'nombre' => 'Petrona Jiménez',
                'especialidad' => 'Petates y tule',
                'foto' => 'https://images.unsplash.com/photo-1606722590583-6951b5ea92ad?w=200',
                'ubicacion' => 'San Miguel Tenextatiloyan, MX',
                'estado' => 'rechazado',
                'ventas_total' => 0,
                'ventas_items' => 0,
                'rating' => null,
                'destacado' => false,
                'notas_moderacion' => 'No coincide el nombre en identificación con el registro.',
            ],
            [
                'id' => 23,
                'nombre' => 'Anselmo Vargas',
                'especialidad' => 'Chocolate artesanal',
                'foto' => 'https://images.unsplash.com/photo-1511381939415-e44015466834?w=200',
                'ubicacion' => 'Villahermosa, MX',
                'estado' => 'aprobado',
                'ventas_total' => 11200.00,
                'ventas_items' => 103,
                'rating' => 4.7,
                'destacado' => true,
                'notas_moderacion' => '',
            ],
            [
                'id' => 24,
                'nombre' => 'Guadalupe Zárate',
                'especialidad' => 'Dulces tradicionales',
                'foto' => 'https://images.unsplash.com/photo-1571506165871-ee72a35bc9d4?w=200',
                'ubicacion' => 'Puebla, MX',
                'estado' => 'nueva',
                'ventas_total' => 0,
                'ventas_items' => 0,
                'rating' => null,
                'destacado' => false,
                'notas_moderacion' => '',
            ],
            [
                'id' => 25,
                'nombre' => 'Higinio Cortés',
                'especialidad' => 'Arte en popote',
                'foto' => 'https://images.unsplash.com/photo-1598899246544-cc9c0be8f2a9?w=200',
                'ubicacion' => 'Tzintzuntzan, MX',
                'estado' => 'revision',
                'ventas_total' => 0,
                'ventas_items' => 0,
                'rating' => null,
                'destacado' => false,
                'notas_moderacion' => '',
            ],
            [
                'id' => 26,
                'nombre' => 'Dolores Mendoza',
                'especialidad' => 'Sombrerería de palma',
                'foto' => 'https://images.unsplash.com/photo-1521369909029-2afed882baee?w=200',
                'ubicacion' => 'Bécal, MX',
                'estado' => 'aprobado',
                'ventas_total' => 5480.00,
                'ventas_items' => 46,
                'rating' => 4.3,
                'destacado' => false,
                'notas_moderacion' => '',
            ],
            [
                'id' => 27,
                'nombre' => 'Amalia Ríos',
                'especialidad' => 'Bordado indígena',
                'foto' => 'https://images.unsplash.com/photo-1541199249251-f713e6145474?w=200',
                'ubicacion' => 'San Pedro Amuzgos, MX',
                'estado' => 'aprobado',
                'ventas_total' => 13950.00,
                'ventas_items' => 112,
                'rating' => 4.9,
                'destacado' => true,
                'notas_moderacion' => '',
            ],
        ];
    }
}
