<?php

namespace Database\Seeders;

use App\Models\Campana;
use App\Models\Carrito;
use App\Models\Cupon;
use App\Models\CuponCanjeado;
use App\Models\DetalleCampana;
use App\Models\DetalleCarrito;
use App\Models\DetalleInventario;
use App\Models\DetalleVenta;
use App\Models\Direccion;
use App\Models\Envio;
use App\Models\Estado;
use App\Models\FormaPago;
use App\Models\Inventario;
use App\Models\Resena;
use App\Models\User;
use App\Models\Vendedor;
use App\Models\Venta;
use App\Models\Tienda;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
            // Catálogo principal: categorías, tiendas, artesanos, artículos e imágenes reales de Oaxaca.
            CatalogoOaxacaSeeder::class,
        ]);

        // Tablas auxiliares (aún con factories; no son el catálogo visible principal).
        FormaPago::factory(5)->create();
        Estado::factory(5)->create();
        Carrito::factory(10)->create();
        Resena::factory(20)->create();
        Inventario::factory(20)->create();
        Venta::factory(10)->create();
        Direccion::factory(10)->create();
        DetalleVenta::factory(20)->create();
        DetalleInventario::factory(20)->create();
        DetalleCarrito::factory(20)->create();
        Envio::factory(10)->create();
        Campana::factory(3)->create();
        DetalleCampana::factory(10)->create();
        Cupon::factory(5)->create();
        CuponCanjeado::factory(5)->create();

        $admin = User::factory()->create([
            'nombre' => 'Admin',
            'apellido_materno' => 'Test',
            'apellido_paterno' => 'User',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole('admin');

        $user = User::factory()->create([
            'nombre' => 'User',
            'apellido_materno' => 'Test',
            'apellido_paterno' => 'User',
            'email' => 'user@example.com',
        ]);
        $user->assignRole('user');

        $vendedorUser = User::factory()->create([
            'nombre' => 'Vendedor',
            'apellido_materno' => 'Test',
            'apellido_paterno' => 'Vendedor',
            'email' => 'vendedor@example.com',
            'password' => Hash::make('password'),
        ]);
        $vendedorUser->assignRole('vendedor');

        $tiendaExistente = Tienda::first();

        if ($tiendaExistente) {
            Vendedor::create([
                'user_id' => $vendedorUser->id,
                'tienda_id' => $tiendaExistente->id,
                'codigo_ine' => 'VEND'.strtoupper(substr(md5((string) time()), 0, 9)),
                'foto_frontal_ine_link' => 'https://picsum.photos/id/64/640/480',
                'foto_trasera_ine_link' => 'https://picsum.photos/id/65/640/480',
                'estatus' => 'activo',
            ]);

            $this->command?->info("✅ Vendedor creado → {$vendedorUser->email}");
        } else {
            $this->command?->warn('⚠️ No se encontró tienda para vincular al vendedor.');
        }
    }
}
