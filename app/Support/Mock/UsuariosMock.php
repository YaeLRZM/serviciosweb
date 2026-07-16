<?php

namespace App\Support\Mock;

class UsuariosMock
{
    private const SESSION_KEY = 'mock_usuarios';

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

        $nuevo = [
            'id' => $items->max('id') + 1,
            'nombre' => $data['nombre'] ?? $data['name'] ?? '',
            'nombre_raw' => $data['nombre'] ?? $data['name'] ?? '',
            'apellido_paterno' => null,
            'apellido_materno' => null,
            'email' => $data['email'] ?? '',
            'rol' => $data['rol'] ?? 'user',
            'estatus' => $data['estatus'] ?? 'activo',
            'created_at' => now()->toIso8601String(),
            'name' => $data['nombre'] ?? $data['name'] ?? '',
        ];

        session([self::SESSION_KEY => $items->push($nuevo)->values()->all()]);

        return $nuevo;
    }

    public static function actualizar(int $id, array $data): array
    {
        $actualizado = null;

        $items = collect(self::all())->map(function ($item) use ($id, $data, &$actualizado) {
            if ((int) $item['id'] !== $id) {
                return $item;
            }

            $nombre = $data['nombre'] ?? $data['name'] ?? $item['nombre'];

            $item = array_merge($item, array_filter([
                'nombre' => $nombre,
                'nombre_raw' => $nombre,
                'name' => $nombre,
                'email' => $data['email'] ?? $item['email'],
                'rol' => $data['rol'] ?? $item['rol'],
                'estatus' => $data['estatus'] ?? $item['estatus'],
            ], fn ($v) => $v !== null));

            $actualizado = $item;

            return $item;
        })->values()->all();

        session([self::SESSION_KEY => $items]);

        return $actualizado ?? throw new \RuntimeException('Usuario no encontrado.');
    }

    /**
     * Forma de cada item (mismo contrato que UsuariosDataService::mapearParaVista):
     * id, nombre, nombre_raw, apellido_paterno, apellido_materno, email, rol, estatus, created_at, name
     */
    private static function datosIniciales(): array
    {
        return [
            [
                'id' => 1,
                'nombre' => 'Ana Beltrán',
                'nombre_raw' => 'Ana',
                'apellido_paterno' => 'Beltrán',
                'apellido_materno' => 'Ríos',
                'email' => 'ana.beltran@example.mx',
                'rol' => 'admin',
                'estatus' => 'activo',
                'created_at' => now()->subMonths(8)->toIso8601String(),
                'name' => 'Ana Beltrán',
            ],
            [
                'id' => 2,
                'nombre' => 'Marco Solís',
                'nombre_raw' => 'Marco',
                'apellido_paterno' => 'Solís',
                'apellido_materno' => null,
                'email' => 'marco.solis@example.mx',
                'rol' => 'user',
                'estatus' => 'activo',
                'created_at' => now()->subDays(3)->toIso8601String(),
                'name' => 'Marco Solís',
            ],
            [
                'id' => 3,
                'nombre' => 'Karla Vidal',
                'nombre_raw' => 'Karla',
                'apellido_paterno' => 'Vidal',
                'apellido_materno' => 'Nuñez',
                'email' => 'karla.vidal@example.mx',
                'rol' => 'user',
                'estatus' => 'suspendido',
                'created_at' => now()->subMonths(2)->toIso8601String(),
                'name' => 'Karla Vidal',
            ],
            [
                'id' => 4,
                'nombre' => 'Diego Fuentes',
                'nombre_raw' => 'Diego',
                'apellido_paterno' => 'Fuentes',
                'apellido_materno' => null,
                'email' => 'diego.fuentes@example.mx',
                'rol' => 'guest',
                'estatus' => 'activo',
                'created_at' => now()->subDays(1)->toIso8601String(),
                'name' => 'Diego Fuentes',
            ],
            [
                'id' => 5,
                'nombre' => 'Renata Ochoa',
                'nombre_raw' => 'Renata',
                'apellido_paterno' => 'Ochoa',
                'apellido_materno' => 'Campos',
                'email' => 'renata.ochoa@example.mx',
                'rol' => 'user',
                'estatus' => 'marcado',
                'created_at' => now()->subMonths(5)->toIso8601String(),
                'name' => 'Renata Ochoa',
            ],
            [
                'id' => 6,
                'nombre' => 'Héctor Paredes',
                'nombre_raw' => 'Héctor',
                'apellido_paterno' => 'Paredes',
                'apellido_materno' => null,
                'email' => 'hector.paredes@example.mx',
                'rol' => 'admin',
                'estatus' => 'activo',
                'created_at' => now()->subYear()->toIso8601String(),
                'name' => 'Héctor Paredes',
            ],
            [
                'id' => 7,
                'nombre' => 'Valeria Camacho',
                'nombre_raw' => 'Valeria',
                'apellido_paterno' => 'Camacho',
                'apellido_materno' => 'Luna',
                'email' => 'valeria.camacho@example.mx',
                'rol' => 'user',
                'estatus' => 'activo',
                'created_at' => now()->subDays(12)->toIso8601String(),
                'name' => 'Valeria Camacho',
            ],
            [
                'id' => 8,
                'nombre' => 'Ricardo Nava',
                'nombre_raw' => 'Ricardo',
                'apellido_paterno' => 'Nava',
                'apellido_materno' => null,
                'email' => 'ricardo.nava@example.mx',
                'rol' => 'user',
                'estatus' => 'activo',
                'created_at' => now()->subDays(45)->toIso8601String(),
                'name' => 'Ricardo Nava',
            ],
            [
                'id' => 9,
                'nombre' => 'Fernanda Cordero',
                'nombre_raw' => 'Fernanda',
                'apellido_paterno' => 'Cordero',
                'apellido_materno' => 'Salgado',
                'email' => 'fernanda.cordero@example.mx',
                'rol' => 'guest',
                'estatus' => 'activo',
                'created_at' => now()->subDays(2)->toIso8601String(),
                'name' => 'Fernanda Cordero',
            ],
            [
                'id' => 10,
                'nombre' => 'Emilio Reyes',
                'nombre_raw' => 'Emilio',
                'apellido_paterno' => 'Reyes',
                'apellido_materno' => null,
                'email' => 'emilio.reyes@example.mx',
                'rol' => 'user',
                'estatus' => 'suspendido',
                'created_at' => now()->subMonths(3)->toIso8601String(),
                'name' => 'Emilio Reyes',
            ],
            [
                'id' => 11,
                'nombre' => 'Paola Zamora',
                'nombre_raw' => 'Paola',
                'apellido_paterno' => 'Zamora',
                'apellido_materno' => 'Delgado',
                'email' => 'paola.zamora@example.mx',
                'rol' => 'user',
                'estatus' => 'activo',
                'created_at' => now()->subDays(20)->toIso8601String(),
                'name' => 'Paola Zamora',
            ],
            [
                'id' => 12,
                'nombre' => 'Gustavo Estrada',
                'nombre_raw' => 'Gustavo',
                'apellido_paterno' => 'Estrada',
                'apellido_materno' => null,
                'email' => 'gustavo.estrada@example.mx',
                'rol' => 'guest',
                'estatus' => 'marcado',
                'created_at' => now()->subMonths(6)->toIso8601String(),
                'name' => 'Gustavo Estrada',
            ],
            [
                'id' => 13,
                'nombre' => 'Lorena Aragón',
                'nombre_raw' => 'Lorena',
                'apellido_paterno' => 'Aragón',
                'apellido_materno' => 'Vega',
                'email' => 'lorena.aragon@example.mx',
                'rol' => 'user',
                'estatus' => 'activo',
                'created_at' => now()->subDays(7)->toIso8601String(),
                'name' => 'Lorena Aragón',
            ],
            [
                'id' => 14,
                'nombre' => 'Iván Cabrera',
                'nombre_raw' => 'Iván',
                'apellido_paterno' => 'Cabrera',
                'apellido_materno' => null,
                'email' => 'ivan.cabrera@example.mx',
                'rol' => 'user',
                'estatus' => 'activo',
                'created_at' => now()->subDays(30)->toIso8601String(),
                'name' => 'Iván Cabrera',
            ],
            [
                'id' => 15,
                'nombre' => 'Daniela Montes',
                'nombre_raw' => 'Daniela',
                'apellido_paterno' => 'Montes',
                'apellido_materno' => 'Rivas',
                'email' => 'daniela.montes@example.mx',
                'rol' => 'admin',
                'estatus' => 'activo',
                'created_at' => now()->subMonths(10)->toIso8601String(),
                'name' => 'Daniela Montes',
            ],
            [
                'id' => 16,
                'nombre' => 'Sebastián Guzmán',
                'nombre_raw' => 'Sebastián',
                'apellido_paterno' => 'Guzmán',
                'apellido_materno' => null,
                'email' => 'sebastian.guzman@example.mx',
                'rol' => 'user',
                'estatus' => 'suspendido',
                'created_at' => now()->subMonths(4)->toIso8601String(),
                'name' => 'Sebastián Guzmán',
            ],
            [
                'id' => 17,
                'nombre' => 'Camila Pineda',
                'nombre_raw' => 'Camila',
                'apellido_paterno' => 'Pineda',
                'apellido_materno' => 'Osorio',
                'email' => 'camila.pineda@example.mx',
                'rol' => 'guest',
                'estatus' => 'activo',
                'created_at' => now()->subDays(5)->toIso8601String(),
                'name' => 'Camila Pineda',
            ],
            [
                'id' => 18,
                'nombre' => 'Rodrigo Villaseñor',
                'nombre_raw' => 'Rodrigo',
                'apellido_paterno' => 'Villaseñor',
                'apellido_materno' => null,
                'email' => 'rodrigo.villasenor@example.mx',
                'rol' => 'user',
                'estatus' => 'activo',
                'created_at' => now()->subDays(60)->toIso8601String(),
                'name' => 'Rodrigo Villaseñor',
            ],
            [
                'id' => 19,
                'nombre' => 'Alejandra Núñez',
                'nombre_raw' => 'Alejandra',
                'apellido_paterno' => 'Núñez',
                'apellido_materno' => 'Rosales',
                'email' => 'alejandra.nunez@example.mx',
                'rol' => 'user',
                'estatus' => 'activo',
                'created_at' => now()->subDays(9)->toIso8601String(),
                'name' => 'Alejandra Núñez',
            ],
            [
                'id' => 20,
                'nombre' => 'Tomás Beltrán',
                'nombre_raw' => 'Tomás',
                'apellido_paterno' => 'Beltrán',
                'apellido_materno' => null,
                'email' => 'tomas.beltran@example.mx',
                'rol' => 'user',
                'estatus' => 'activo',
                'created_at' => now()->subDays(75)->toIso8601String(),
                'name' => 'Tomás Beltrán',
            ],
            [
                'id' => 21,
                'nombre' => 'Mariana Cortés',
                'nombre_raw' => 'Mariana',
                'apellido_paterno' => 'Cortés',
                'apellido_materno' => 'Ibarra',
                'email' => 'mariana.cortes@example.mx',
                'rol' => 'guest',
                'estatus' => 'activo',
                'created_at' => now()->subDays(18)->toIso8601String(),
                'name' => 'Mariana Cortés',
            ],
            [
                'id' => 22,
                'nombre' => 'Julián Espinoza',
                'nombre_raw' => 'Julián',
                'apellido_paterno' => 'Espinoza',
                'apellido_materno' => null,
                'email' => 'julian.espinoza@example.mx',
                'rol' => 'user',
                'estatus' => 'suspendido',
                'created_at' => now()->subMonths(7)->toIso8601String(),
                'name' => 'Julián Espinoza',
            ],
            [
                'id' => 23,
                'nombre' => 'Ximena Rangel',
                'nombre_raw' => 'Ximena',
                'apellido_paterno' => 'Rangel',
                'apellido_materno' => 'Peña',
                'email' => 'ximena.rangel@example.mx',
                'rol' => 'user',
                'estatus' => 'activo',
                'created_at' => now()->subDays(40)->toIso8601String(),
                'name' => 'Ximena Rangel',
            ],
            [
                'id' => 24,
                'nombre' => 'Andrés Ponce',
                'nombre_raw' => 'Andrés',
                'apellido_paterno' => 'Ponce',
                'apellido_materno' => null,
                'email' => 'andres.ponce@example.mx',
                'rol' => 'admin',
                'estatus' => 'activo',
                'created_at' => now()->subMonths(11)->toIso8601String(),
                'name' => 'Andrés Ponce',
            ],
            [
                'id' => 25,
                'nombre' => 'Bianca Robles',
                'nombre_raw' => 'Bianca',
                'apellido_paterno' => 'Robles',
                'apellido_materno' => 'Fajardo',
                'email' => 'bianca.robles@example.mx',
                'rol' => 'user',
                'estatus' => 'marcado',
                'created_at' => now()->subDays(22)->toIso8601String(),
                'name' => 'Bianca Robles',
            ],
            [
                'id' => 26,
                'nombre' => 'Leonardo Mejía',
                'nombre_raw' => 'Leonardo',
                'apellido_paterno' => 'Mejía',
                'apellido_materno' => null,
                'email' => 'leonardo.mejia@example.mx',
                'rol' => 'user',
                'estatus' => 'activo',
                'created_at' => now()->subDays(3)->toIso8601String(),
                'name' => 'Leonardo Mejía',
            ],
            [
                'id' => 27,
                'nombre' => 'Regina Cisneros',
                'nombre_raw' => 'Regina',
                'apellido_paterno' => 'Cisneros',
                'apellido_materno' => 'Domínguez',
                'email' => 'regina.cisneros@example.mx',
                'rol' => 'guest',
                'estatus' => 'activo',
                'created_at' => now()->subDays(29)->toIso8601String(),
                'name' => 'Regina Cisneros',
            ],
            [
                'id' => 28,
                'nombre' => 'Omar Trejo',
                'nombre_raw' => 'Omar',
                'apellido_paterno' => 'Trejo',
                'apellido_materno' => null,
                'email' => 'omar.trejo@example.mx',
                'rol' => 'user',
                'estatus' => 'activo',
                'created_at' => now()->subMonths(1)->toIso8601String(),
                'name' => 'Omar Trejo',
            ],
            [
                'id' => 29,
                'nombre' => 'Constanza Herrera',
                'nombre_raw' => 'Constanza',
                'apellido_paterno' => 'Herrera',
                'apellido_materno' => 'Bravo',
                'email' => 'constanza.herrera@example.mx',
                'rol' => 'user',
                'estatus' => 'suspendido',
                'created_at' => now()->subDays(55)->toIso8601String(),
                'name' => 'Constanza Herrera',
            ],
            [
                'id' => 30,
                'nombre' => 'Bruno Salcedo',
                'nombre_raw' => 'Bruno',
                'apellido_paterno' => 'Salcedo',
                'apellido_materno' => null,
                'email' => 'bruno.salcedo@example.mx',
                'rol' => 'admin',
                'estatus' => 'activo',
                'created_at' => now()->subMonths(13)->toIso8601String(),
                'name' => 'Bruno Salcedo',
            ],
        ];
    }
}
