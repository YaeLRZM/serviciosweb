<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Categoria;
use App\Models\Carrito;
use App\Models\Tienda;
use App\Models\FormaPago;
use App\Models\Estado;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Categoria::factory(5)->create();
        Tienda::factory(5)->create();
        FormaPago::factory(5)->create();
        Estado::factory(5)->create();
        Carrito::factory(10)->create();
        

        User::factory()->create([
            'nombre' => 'example',
            'apellido_materno' => 'Test',
            'apellido_paterno' => 'User',
            'email' => 'test@example.com',
        ]);

    }
}
