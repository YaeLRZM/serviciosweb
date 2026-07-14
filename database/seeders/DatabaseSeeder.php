<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Categoria;
use App\Models\Carrito;
use App\Models\Tienda;
use App\Models\FormaPago;
use App\Models\Estado;
use App\Models\Artesano;
use App\Models\Articulo;
use App\Models\Resena;
use App\Models\Inventario;
use App\Models\Direccion;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\DetalleInventario;
use App\Models\Vendedor;
use App\Models\DetalleCarrito;
use App\Models\Envio;
use App\Models\Campana;
use App\Models\DetalleCampana;
use App\Models\Cupon;
use App\Models\CuponCanjeado;
use App\Models\ImagenArticulo;

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
        Categoria::factory(5)->create();
        Tienda::factory(5)->create();
        FormaPago::factory(5)->create();
        Estado::factory(5)->create();
        Carrito::factory(10)->create();
        Articulo::factory(50)->create();
        Artesano::factory(10)->create();
        Resena::factory(50)->create();
        Inventario::factory(50)->create();
        Venta::factory(20)->create();
        Direccion::factory(10)->create();
        DetalleVenta::factory(50)->create();
        DetalleInventario::factory(50)->create();
        Vendedor::factory(10)->create();
        DetalleCarrito::factory(50)->create();
        Envio::factory(20)->create();
        Campana::factory(5)->create();
        DetalleCampana::factory(50)->create();
        Cupon::factory(10)->create();
        CuponCanjeado::factory(10)->create();
        ImagenArticulo::factory(50)->create();
        User::factory()->create([
            'nombre' => 'example',
            'apellido_materno' => 'Test',
            'apellido_paterno' => 'User',
            'email' => 'test@example.com',
        ]);
        
    }
}
