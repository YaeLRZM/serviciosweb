<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // ARTICULOS 
            'verArticulos',
            'crearArticulos',
            'editarArticulos',
            'eliminarArticulos',
            // CATEGORIAS 
            'crearCategorias',
            'editarCategorias',
            'eliminarCategorias',
            // ARTESANOS 
            'crearArtesanos',
            'editarArtesanos',
            'eliminarArtesanos',
            // TIENDAS 
            'crearTiendas',
            'editarTiendas',
            'eliminarTiendas',
            // RESEÑAS
            'verResenas',
            'crearResenas',
            'editarResenas',
            'eliminarResenas',
            // USUARIOS
            'verUsuarios',
            'crearUsuarios',
            'editarUsuarios',
            'eliminarUsuarios',
            // COMPRAS
            'verCompras',
            'crearCompras',
            'editarCompras',
            'eliminarCompras',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions($permissions);

        $user = Role::firstOrCreate(['name' => 'user']);
        $user->syncPermissions([
            'verArticulos',
            'verResenas',
            'crearResenas',
            'editarResenas',
            'eliminarResenas',
            'verCompras',
            'crearCompras',
            'editarCompras',
            'eliminarCompras',
        ]);

        $vendedor = Role::firstOrCreate(['name' => 'vendedor']);
        $vendedor->syncPermissions([
            'verArticulos',
            'crearArticulos',
            'editarArticulos',
            'eliminarArticulos',
            // Solo editar (no crear/eliminar) tiendas: ownership en UpdateTiendaRequest.
            'editarTiendas',
            'verResenas',
            'crearResenas',
            'editarResenas',
            'eliminarResenas',
            'verCompras',
            'crearCompras',
            'editarCompras',
            'eliminarCompras',
        ]);

        $guest = Role::firstOrCreate(['name' => 'guest']);
        $guest->syncPermissions([
            'verArticulos',
            'verResenas',
        ]);
    }
}
