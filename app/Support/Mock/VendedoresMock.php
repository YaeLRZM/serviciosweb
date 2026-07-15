<?php

namespace App\Support\Mock;

class VendedoresMock
{
    private const SESSION_KEY = 'mock_vendedores';

    public static function all(): array
    {
        if (! session()->has(self::SESSION_KEY)) {
            session([self::SESSION_KEY => self::datosIniciales()]);
        }

        return session(self::SESSION_KEY);
    }

    public static function solicitudes(): array
    {
        return collect(self::all())
            ->where('estatus', 'En Revisión')
            ->values()
            ->all();
    }

    public static function find(int $id): ?array
    {
        return collect(self::all())->firstWhere('id', $id);
    }

    public static function actualizarEstatus(int $id, string $estatus): void
    {
        $items = collect(self::all())->map(function ($item) use ($id, $estatus) {
            if ((int) $item['id'] === $id) {
                $item['estatus'] = $estatus;
            }

            return $item;
        })->values()->toArray();

        session([self::SESSION_KEY => $items]);
    }

    /**
     * Forma de cada item (contrato UI / esperado del API):
     * id, tienda, propietario, email, imagen, categoria, ingreso, rating, resenas,
     * estatus, reportado, codigo_ine, foto_frontal_ine, foto_trasera_ine, notas
     */
    private static function datosIniciales(): array
    {
        return [
            [
                'id' => 1,
                'tienda' => 'Arte en Filigrana',
                'propietario' => 'Sofia Mendoza',
                'email' => 'sofia.mendoza@example.mx',
                'imagen' => 'https://images.unsplash.com/photo-1611085583191-a3b181a88401?w=200',
                'categoria' => 'Joyería',
                'ingreso' => '12 Mar 2023',
                'rating' => 4.9,
                'resenas' => 124,
                'estatus' => 'Verificado',
                'reportado' => false,
                'codigo_ine' => 'SMEN850312MDFNNS01',
                'foto_frontal_ine' => null,
                'foto_trasera_ine' => null,
                'notas' => '',
            ],
            [
                'id' => 2,
                'tienda' => 'Talabartería Oaxaca',
                'propietario' => 'Carlos Vazquez',
                'email' => 'carlos.vazquez@example.mx',
                'imagen' => 'https://images.unsplash.com/photo-1523293182086-7651a899d37f?w=200',
                'categoria' => 'Cuero',
                'ingreso' => '05 Ene 2024',
                'rating' => null,
                'resenas' => 0,
                'estatus' => 'En Revisión',
                'reportado' => false,
                'codigo_ine' => 'CVAZ900101HOCRRL09',
                'foto_frontal_ine' => null,
                'foto_trasera_ine' => null,
                'notas' => '',
            ],
            [
                'id' => 3,
                'tienda' => 'Sabores del Sur',
                'propietario' => 'Ricardo Gomez',
                'email' => 'ricardo.gomez@example.mx',
                'imagen' => 'https://images.unsplash.com/photo-1547592180-85f173990554?w=200',
                'categoria' => 'Gastronomía',
                'ingreso' => '22 Nov 2022',
                'rating' => 4.2,
                'resenas' => 312,
                'estatus' => 'Suspendido',
                'reportado' => true,
                'codigo_ine' => 'RGOM880215HDFMRC02',
                'foto_frontal_ine' => null,
                'foto_trasera_ine' => null,
                'notas' => '',
            ],
            [
                'id' => 4,
                'tienda' => 'Bordados Juchitán',
                'propietario' => 'Ximena Morales',
                'email' => 'ximena.morales@example.mx',
                'imagen' => 'https://images.unsplash.com/photo-1610030181087-540f5b32c235?w=200',
                'categoria' => 'Textiles',
                'ingreso' => '15 Sep 2023',
                'rating' => 5.0,
                'resenas' => 89,
                'estatus' => 'Verificado',
                'reportado' => false,
                'codigo_ine' => 'XMOR920630MOCRXM08',
                'foto_frontal_ine' => null,
                'foto_trasera_ine' => null,
                'notas' => '',
            ],
            [
                'id' => 5,
                'tienda' => 'Barro Rojo San Marcos',
                'propietario' => 'Elena Juarez',
                'email' => 'elena.juarez@example.mx',
                'imagen' => 'https://images.unsplash.com/photo-1565193566173-7a0ee3dbe261?w=200',
                'categoria' => 'Cerámica',
                'ingreso' => '02 Feb 2024',
                'rating' => null,
                'resenas' => 0,
                'estatus' => 'En Revisión',
                'reportado' => false,
                'codigo_ine' => 'EJUA870411MDFRLN05',
                'foto_frontal_ine' => null,
                'foto_trasera_ine' => null,
                'notas' => '',
            ],
            [
                'id' => 6,
                'tienda' => 'Tejidos del Valle',
                'propietario' => 'Mateo Ruiz',
                'email' => 'mateo.ruiz@example.mx',
                'imagen' => 'https://images.unsplash.com/photo-1595408076683-577a0e414ed3?w=200',
                'categoria' => 'Textiles',
                'ingreso' => '18 Feb 2024',
                'rating' => null,
                'resenas' => 0,
                'estatus' => 'En Revisión',
                'reportado' => true,
                'codigo_ine' => 'MRUI950920HOCZTM03',
                'foto_frontal_ine' => null,
                'foto_trasera_ine' => null,
                'notas' => '',
            ],
            [
                'id' => 7,
                'tienda' => 'Taller de Alebrijes',
                'propietario' => 'Isabela Cruz',
                'email' => 'isabela.cruz@example.mx',
                'imagen' => 'https://images.unsplash.com/photo-1544829099-b9a0c07fad1a?w=200',
                'categoria' => 'Talla en madera',
                'ingreso' => '01 Mar 2024',
                'rating' => null,
                'resenas' => 0,
                'estatus' => 'En Revisión',
                'reportado' => false,
                'codigo_ine' => 'ICRU930715MDFRZB07',
                'foto_frontal_ine' => null,
                'foto_trasera_ine' => null,
                'notas' => '',
            ],
        ];
    }
}
