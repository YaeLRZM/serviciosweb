<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ARTICULOS
        Permission::firstOrCreate(['name' => 'verArticulos']);
        Permission::firstOrCreate(['name' => 'crearArticulos']);
        Permission::firstOrCreate(['name' => 'editarArticulos']);
        Permission::firstOrCreate(['name' => 'eliminarArticulos']);
        // RESEÑAS
        Permission::firstOrCreate(['name' => 'verResenas']);
        Permission::firstOrCreate(['name' => 'crearResenas']);
        Permission::firstOrCreate(['name' => 'editarResenas']);
        Permission::firstOrCreate(['name' => 'eliminarResenas']);
        // USUARIOS
        Permission::firstOrCreate(['name' => 'verUsuarios']);
        Permission::firstOrCreate(['name' => 'crearUsuarios']);
        Permission::firstOrCreate(['name' => 'editarUsuarios']);
        Permission::firstOrCreate(['name' => 'eliminarUsuarios']);
        // COMPRAS
        Permission::firstOrCreate(['name' => 'verCompras']);
        Permission::firstOrCreate(['name' => 'crearCompras']);
        Permission::firstOrCreate(['name' => 'editarCompras']);
        Permission::firstOrCreate(['name' => 'eliminarCompras']);

        // ADMIN
        Role::create(['name' => 'admin'])->givePermissionTo([
            'verArticulos',
            'crearArticulos',
            'editarArticulos',
            'eliminarArticulos',
            'verResenas',
            'crearResenas',
            'editarResenas',
            'eliminarResenas',
            'verUsuarios',
            'crearUsuarios',
            'editarUsuarios',
            'eliminarUsuarios',
            'verCompras',
            'crearCompras',
            'editarCompras',
            'eliminarCompras'
        ]);
        // USER
        Role::create(['name' => 'user'])->givePermissionTo([
            'verArticulos',
            'verResenas',
            'crearResenas',
            'editarResenas',
            'eliminarResenas',
            'verCompras',
            'crearCompras',
            'editarCompras',
            'eliminarCompras'
        ]);
        // GUEST
        Role::create(['name' => 'guest'])->givePermissionTo([
            'verArticulos'
        ]);
    }
}
// 
