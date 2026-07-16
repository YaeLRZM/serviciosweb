<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(RolesSeeder::class);

        $usuarios = [
            [
                'nombre' => 'Ana',
                'apellido_paterno' => 'Beltrán',
                'apellido_materno' => 'Ríos',
                'email' => 'admin@example.com',
                'rol' => 'admin',
                'estatus' => 'activo',
            ],
            [
                'nombre' => 'Marco',
                'apellido_paterno' => 'Solís',
                'apellido_materno' => null,
                'email' => 'marco.solis@example.mx',
                'rol' => 'user',
                'estatus' => 'activo',
            ],
            [
                'nombre' => 'Karla',
                'apellido_paterno' => 'Vidal',
                'apellido_materno' => 'Nuñez',
                'email' => 'karla.vidal@example.mx',
                'rol' => 'user',
                'estatus' => 'suspendido',
            ],
            [
                'nombre' => 'Diego',
                'apellido_paterno' => 'Fuentes',
                'apellido_materno' => null,
                'email' => 'diego.fuentes@example.mx',
                'rol' => 'guest',
                'estatus' => 'activo',
            ],
            [
                'nombre' => 'Renata',
                'apellido_paterno' => 'Ochoa',
                'apellido_materno' => 'Campos',
                'email' => 'renata.ochoa@example.mx',
                'rol' => 'user',
                'estatus' => 'marcado',
            ],
            [
                'nombre' => 'Héctor',
                'apellido_paterno' => 'Paredes',
                'apellido_materno' => null,
                'email' => 'hector.paredes@example.mx',
                'rol' => 'admin',
                'estatus' => 'activo',
            ],
            [
                'nombre' => 'Valeria',
                'apellido_paterno' => 'Camacho',
                'apellido_materno' => 'Luna',
                'email' => 'valeria.camacho@example.mx',
                'rol' => 'user',
                'estatus' => 'activo',
            ],
            [
                'nombre' => 'Ricardo',
                'apellido_paterno' => 'Nava',
                'apellido_materno' => null,
                'email' => 'ricardo.nava@example.mx',
                'rol' => 'user',
                'estatus' => 'activo',
            ],
            [
                'nombre' => 'Fernanda',
                'apellido_paterno' => 'Cordero',
                'apellido_materno' => 'Salgado',
                'email' => 'fernanda.cordero@example.mx',
                'rol' => 'guest',
                'estatus' => 'activo',
            ],
            [
                'nombre' => 'Emilio',
                'apellido_paterno' => 'Reyes',
                'apellido_materno' => null,
                'email' => 'emilio.reyes@example.mx',
                'rol' => 'user',
                'estatus' => 'suspendido',
            ],
            [
                'nombre' => 'Paola',
                'apellido_paterno' => 'Zamora',
                'apellido_materno' => 'Delgado',
                'email' => 'paola.zamora@example.mx',
                'rol' => 'user',
                'estatus' => 'activo',
            ],
            [
                'nombre' => 'Gustavo',
                'apellido_paterno' => 'Estrada',
                'apellido_materno' => null,
                'email' => 'gustavo.estrada@example.mx',
                'rol' => 'guest',
                'estatus' => 'marcado',
            ],
            [
                'nombre' => 'Lorena',
                'apellido_paterno' => 'Aragón',
                'apellido_materno' => 'Vega',
                'email' => 'lorena.aragon@example.mx',
                'rol' => 'user',
                'estatus' => 'activo',
            ],
            [
                'nombre' => 'Iván',
                'apellido_paterno' => 'Cabrera',
                'apellido_materno' => null,
                'email' => 'ivan.cabrera@example.mx',
                'rol' => 'user',
                'estatus' => 'activo',
            ],
            [
                'nombre' => 'Daniela',
                'apellido_paterno' => 'Montes',
                'apellido_materno' => 'Rivas',
                'email' => 'daniela.montes@example.mx',
                'rol' => 'admin',
                'estatus' => 'activo',
            ],
            [
                'nombre' => 'Sebastián',
                'apellido_paterno' => 'Guzmán',
                'apellido_materno' => null,
                'email' => 'sebastian.guzman@example.mx',
                'rol' => 'user',
                'estatus' => 'suspendido',
            ],
            [
                'nombre' => 'Camila',
                'apellido_paterno' => 'Pineda',
                'apellido_materno' => 'Osorio',
                'email' => 'camila.pineda@example.mx',
                'rol' => 'guest',
                'estatus' => 'activo',
            ],
            [
                'nombre' => 'Rodrigo',
                'apellido_paterno' => 'Villaseñor',
                'apellido_materno' => null,
                'email' => 'rodrigo.villasenor@example.mx',
                'rol' => 'user',
                'estatus' => 'activo',
            ],
            [
                'nombre' => 'Alejandra',
                'apellido_paterno' => 'Núñez',
                'apellido_materno' => 'Rosales',
                'email' => 'alejandra.nunez@example.mx',
                'rol' => 'user',
                'estatus' => 'activo',
            ],
            [
                'nombre' => 'Tomás',
                'apellido_paterno' => 'Beltrán',
                'apellido_materno' => null,
                'email' => 'tomas.beltran@example.mx',
                'rol' => 'user',
                'estatus' => 'activo',
            ],
            [
                'nombre' => 'Mariana',
                'apellido_paterno' => 'Cortés',
                'apellido_materno' => 'Ibarra',
                'email' => 'mariana.cortes@example.mx',
                'rol' => 'guest',
                'estatus' => 'activo',
            ],
            [
                'nombre' => 'Julián',
                'apellido_paterno' => 'Espinoza',
                'apellido_materno' => null,
                'email' => 'julian.espinoza@example.mx',
                'rol' => 'user',
                'estatus' => 'suspendido',
            ],
            [
                'nombre' => 'Ximena',
                'apellido_paterno' => 'Rangel',
                'apellido_materno' => 'Peña',
                'email' => 'ximena.rangel@example.mx',
                'rol' => 'user',
                'estatus' => 'activo',
            ],
            [
                'nombre' => 'Andrés',
                'apellido_paterno' => 'Ponce',
                'apellido_materno' => null,
                'email' => 'andres.ponce@example.mx',
                'rol' => 'admin',
                'estatus' => 'activo',
            ],
            [
                'nombre' => 'Bianca',
                'apellido_paterno' => 'Robles',
                'apellido_materno' => 'Fajardo',
                'email' => 'bianca.robles@example.mx',
                'rol' => 'user',
                'estatus' => 'marcado',
            ],
            [
                'nombre' => 'Leonardo',
                'apellido_paterno' => 'Mejía',
                'apellido_materno' => null,
                'email' => 'leonardo.mejia@example.mx',
                'rol' => 'user',
                'estatus' => 'activo',
            ],
            [
                'nombre' => 'Regina',
                'apellido_paterno' => 'Cisneros',
                'apellido_materno' => 'Domínguez',
                'email' => 'regina.cisneros@example.mx',
                'rol' => 'guest',
                'estatus' => 'activo',
            ],
            [
                'nombre' => 'Omar',
                'apellido_paterno' => 'Trejo',
                'apellido_materno' => null,
                'email' => 'omar.trejo@example.mx',
                'rol' => 'user',
                'estatus' => 'activo',
            ],
            [
                'nombre' => 'Constanza',
                'apellido_paterno' => 'Herrera',
                'apellido_materno' => 'Bravo',
                'email' => 'constanza.herrera@example.mx',
                'rol' => 'user',
                'estatus' => 'suspendido',
            ],
            [
                'nombre' => 'Bruno',
                'apellido_paterno' => 'Salcedo',
                'apellido_materno' => null,
                'email' => 'bruno.salcedo@example.mx',
                'rol' => 'admin',
                'estatus' => 'activo',
            ],
        ];

        // Esquema dual: BD clásica (`name`) vs BD extendida (`nombre` + apellidos).
        $tieneName = Schema::hasColumn('users', 'name');

        foreach ($usuarios as $datos) {
            $rol = $datos['rol'];

            $payload = [
                'email' => $datos['email'],
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'estatus' => $datos['estatus'],
            ];

            if ($tieneName) {
                $payload['name'] = trim($datos['nombre'] . ' ' . $datos['apellido_paterno'] . ' ' . ($datos['apellido_materno'] ?? ''));
            } else {
                $payload['nombre'] = $datos['nombre'];
                $payload['apellido_paterno'] = $datos['apellido_paterno'];
                $payload['apellido_materno'] = $datos['apellido_materno'];
            }

            $usuario = new User;
            $usuario->forceFill($payload)->save();
            $usuario->assignRole($rol);
        }
    }
}
