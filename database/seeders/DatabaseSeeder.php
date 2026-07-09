<?php

namespace Database\Seeders;

use App\Models\Articulo;
use App\Models\Compra;
use App\Models\Resena;
use App\Models\User;
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
        $this->call([
            RolesSeeder::class,
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $user = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('user');

        $guest = User::factory()->create([
            'name' => 'Guest User',
            'email' => 'guest@example.com',
            'password' => bcrypt('password'),
        ]);
        $guest->assignRole('guest');

        // Extra users without roles — useful to verify that JWT alone is not enough.
        User::factory(5)->create();

        Articulo::factory(20)->create();
        Resena::factory(30)->create();
        Compra::factory(15)->create();
    }
}
