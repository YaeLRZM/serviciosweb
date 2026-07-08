<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Articulo;
use App\Models\Resena;
use App\Models\Compra;
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
        User::factory(10)->create();

        $admin = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        
        $this->call([
            RolesSeeder::class
        ]);
        $admin->assignRole('admin');

        Articulo::factory(20)->create();
        Resena::factory(30)->create();
        Compra::factory(15)->create();
    }
}
