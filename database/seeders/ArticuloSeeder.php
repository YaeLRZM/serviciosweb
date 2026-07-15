<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Articulo;
use App\Models\Categoria;
use App\Models\Artesano;
use App\Models\Tienda;
use App\Models\ImagenArticulo;

class ArticuloSeeder extends Seeder
{
    public function run(): void
    {
        if (Categoria::count() === 0) {
            Categoria::factory(5)->create();
        }
        if (Artesano::count() === 0) {
            Artesano::factory(5)->create();
        }
        if (Tienda::count() === 0) {
            Tienda::factory(5)->create();
        }

        $categorias = Categoria::pluck('id')->all();
        $artesanos = Artesano::pluck('id')->all();
        $tiendas = Tienda::pluck('id')->all();

       $articulos = [
            [
                'nombre' => 'Huipil bordado de Oaxaca',
                'talla' => 'M',
                'color' => 'Rojo',
                'bordado' => 'Punto de cruz',
                'tela' => 'Manta',
                'region' => 'Oaxaca',
                'imagenes' => [
                    'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=800',
                    'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?w=800',
                ],
            ],
            [
                'nombre' => 'Rebozo de seda de Santa María',
                'talla' => 'Único',
                'color' => 'Azul',
                'bordado' => 'Deshilado',
                'tela' => 'Seda',
                'region' => 'Michoacán',
                'imagenes' => [
                    'https://images.unsplash.com/photo-1544441893-675973e31985?w=800',
                ],
            ],
            [
                'nombre' => 'Blusa de manta con flores',
                'talla' => 'S',
                'color' => 'Blanco',
                'bordado' => 'Floral',
                'tela' => 'Manta',
                'region' => 'Chiapas',
                'imagenes' => [
                    'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?w=800',
                    'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=800',
                ],
            ],
            [
                'nombre' => 'Sarape de lana de Saltillo',
                'talla' => 'L',
                'color' => 'Multicolor',
                'bordado' => 'Telar',
                'tela' => 'Lana',
                'region' => 'Coahuila',
                'imagenes' => [
                    'https://images.unsplash.com/photo-1528459801416-a9e53bbf4e17?w=800',
                ],
            ],
            [
                'nombre' => 'Vestido bordado de Yucatán',
                'talla' => 'M',
                'color' => 'Blanco',
                'bordado' => 'Punto de cruz',
                'tela' => 'Algodón',
                'region' => 'Yucatán',
                'imagenes' => [
                    'https://images.unsplash.com/photo-1596993100471-c3905dafa78e?w=800',
                    'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=800',
                ],
            ],
            [
                'nombre' => 'Camisa de lino artesanal',
                'talla' => 'XL',
                'color' => 'Rojo',
                'bordado' => 'Floral',
                'tela' => 'Lino',
                'region' => 'Oaxaca',
                'imagenes' => [
                    'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=800',
                ],
            ],
        ];

        foreach ($articulos as $data) {
            $imagenes = $data['imagenes'];
            unset($data['imagenes']);

            $articulo = Articulo::create(array_merge($data, [
                'categoria_id' => fake()->randomElement($categorias),
                'artesano_id' => fake()->randomElement($artesanos),
                'tienda_id' => fake()->randomElement($tiendas),
            ]));

            foreach ($imagenes as $i => $url) {
                ImagenArticulo::create([
                    'articulo_id' => $articulo->id,
                    'url' => $url,
                    'es_principal' => $i === 0, 
                ]);
            }
        }
    }
}
