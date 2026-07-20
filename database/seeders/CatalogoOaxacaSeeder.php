<?php

namespace Database\Seeders;

use App\Models\Artesano;
use App\Models\Articulo;
use App\Models\Categoria;
use App\Models\ImagenArticulo;
use App\Models\Tienda;
use Illuminate\Database\Seeder;

/**
 * Catálogo principal de prueba: prendas y textiles de Oaxaca.
 * Reemplaza factories faker para categorías, artesanos, tiendas y artículos visibles.
 */
class CatalogoOaxacaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = $this->seedCategorias();
        $tiendas = $this->seedTiendas();
        $artesanos = $this->seedArtesanos();
        $this->seedArticulos($categorias, $tiendas, $artesanos);
    }

    /**
     * @return array<string, int> nombre => id
     */
    private function seedCategorias(): array
    {
        $defs = [
            [
                'nombre' => 'Huipiles',
                'descripcion' => 'Huipiles bordados a mano de comunidades oaxaqueñas.',
                'imagen' => null,
            ],
            [
                'nombre' => 'Blusas bordadas',
                'descripcion' => 'Blusas de manta y algodón con bordado tradicional.',
                'imagen' => null,
            ],
            [
                'nombre' => 'Rebozos',
                'descripcion' => 'Rebozos de algodón y seda de los valles de Oaxaca.',
                'imagen' => null,
            ],
            [
                'nombre' => 'Vestidos',
                'descripcion' => 'Vestidos y trajes con bordado oaxaqueño.',
                'imagen' => null,
            ],
            [
                'nombre' => 'Camisas artesanales',
                'descripcion' => 'Camisas de manta y lino con detalle artesanal.',
                'imagen' => null,
            ],
            [
                'nombre' => 'Textiles',
                'descripcion' => 'Manteles, caminos de mesa y textiles de telar de cintura.',
                'imagen' => null,
            ],
        ];

        $map = [];
        foreach ($defs as $def) {
            $cat = Categoria::updateOrCreate(
                ['nombre' => $def['nombre']],
                [
                    'descripcion' => $def['descripcion'],
                    'imagen' => $def['imagen'],
                    'visible' => true,
                ]
            );
            $map[$cat->nombre] = $cat->id;
        }

        return $map;
    }

    /**
     * @return array<string, int> nombre => id
     */
    private function seedTiendas(): array
    {
        $defs = [
            [
                'nombre' => 'Textiles del Valle',
                'descripcion' => 'Cooperativa de textiles de los Valles Centrales de Oaxaca.',
                'rfc_moral' => 'TVA850101ABC',
            ],
            [
                'nombre' => 'Casa Mixteca',
                'descripcion' => 'Prendas bordadas de la Mixteca oaxaqueña.',
                'rfc_moral' => 'CMX900215XYZ',
            ],
            [
                'nombre' => 'Telar de Istmo',
                'descripcion' => 'Huipiles y textiles del Istmo de Tehuantepec.',
                'rfc_moral' => 'TDI880320DEF',
            ],
        ];

        $map = [];
        foreach ($defs as $def) {
            $t = Tienda::updateOrCreate(
                ['nombre' => $def['nombre']],
                [
                    'descripcion' => $def['descripcion'],
                    'rfc_moral' => $def['rfc_moral'],
                ]
            );
            $map[$t->nombre] = $t->id;
        }

        return $map;
    }

    /**
     * @return array<string, int> nombre => id
     */
    private function seedArtesanos(): array
    {
        $nombres = [
            'María Sánchez Cruz',
            'Elena López Jiménez',
            'Juana Ruiz Morales',
            'Carmen Vidal Pérez',
            'Rosa Hernández García',
        ];

        $map = [];
        foreach ($nombres as $nombre) {
            $a = Artesano::updateOrCreate(
                ['nombre' => $nombre],
                []
            );
            $map[$a->nombre] = $a->id;
        }

        return $map;
    }

    /**
     * @param  array<string, int>  $categorias
     * @param  array<string, int>  $tiendas
     * @param  array<string, int>  $artesanos
     */
    private function seedArticulos(array $categorias, array $tiendas, array $artesanos): void
    {
        $items = [
            [
                'nombre' => 'Huipil de gala de San Antonino',
                'categoria' => 'Huipiles',
                'tienda' => 'Textiles del Valle',
                'artesano' => 'María Sánchez Cruz',
                'talla' => 'M',
                'color' => 'Rojo',
                'bordado' => 'Punto de cruz',
                'tela' => 'Manta',
                'region' => 'San Antonino Castillo Velasco',
                'precio' => 1850.00,
                'stock' => 8,
                'descripcion' => 'Huipil de gala bordado a mano con hilos de colores, típico de San Antonino.',
                'imagenes' => [
                    'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=800',
                    'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?w=800',
                ],
            ],
            [
                'nombre' => 'Huipil istmeño de Tehuantepec',
                'categoria' => 'Huipiles',
                'tienda' => 'Telar de Istmo',
                'artesano' => 'Juana Ruiz Morales',
                'talla' => 'L',
                'color' => 'Negro',
                'bordado' => 'Cadena',
                'tela' => 'Terciopelo',
                'region' => 'Juchitán de Zaragoza',
                'precio' => 3200.00,
                'stock' => 4,
                'descripcion' => 'Huipil istmeño con bordado de flores en terciopelo, uso ceremonial.',
                'imagenes' => [
                    'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?w=800',
                ],
            ],
            [
                'nombre' => 'Huipil corto de cotón',
                'categoria' => 'Huipiles',
                'tienda' => 'Casa Mixteca',
                'artesano' => 'Elena López Jiménez',
                'talla' => 'S',
                'color' => 'Blanco',
                'bordado' => 'Floral',
                'tela' => 'Algodón',
                'region' => 'Tlaxiaco',
                'precio' => 980.00,
                'stock' => 12,
                'descripcion' => 'Huipil corto de uso diario con bordado floral de la Mixteca.',
                'imagenes' => [
                    'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=800',
                ],
            ],
            [
                'nombre' => 'Blusa de manta con flores de San Antonino',
                'categoria' => 'Blusas bordadas',
                'tienda' => 'Textiles del Valle',
                'artesano' => 'María Sánchez Cruz',
                'talla' => 'M',
                'color' => 'Blanco',
                'bordado' => 'Floral',
                'tela' => 'Manta',
                'region' => 'San Antonino Castillo Velasco',
                'precio' => 750.00,
                'stock' => 15,
                'descripcion' => 'Blusa de manta con bordado floral a mano, cuello redondo.',
                'imagenes' => [
                    'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=800',
                    'https://images.unsplash.com/photo-1596993100471-c3905dafa78e?w=800',
                ],
            ],
            [
                'nombre' => 'Blusa bordada de Tlacolula',
                'categoria' => 'Blusas bordadas',
                'tienda' => 'Textiles del Valle',
                'artesano' => 'Carmen Vidal Pérez',
                'talla' => 'S',
                'color' => 'Azul',
                'bordado' => 'Punto de cruz',
                'tela' => 'Algodón',
                'region' => 'Tlacolula de Matamoros',
                'precio' => 890.00,
                'stock' => 10,
                'descripcion' => 'Blusa de algodón con punto de cruz en tonos azules y rojos.',
                'imagenes' => [
                    'https://images.unsplash.com/photo-1528459801416-a9e53bbf4e17?w=800',
                ],
            ],
            [
                'nombre' => 'Blusa de pechera zapoteca',
                'categoria' => 'Blusas bordadas',
                'tienda' => 'Casa Mixteca',
                'artesano' => 'Rosa Hernández García',
                'talla' => 'L',
                'color' => 'Rojo',
                'bordado' => 'Geométrico',
                'tela' => 'Manta',
                'region' => 'Teotitlán del Valle',
                'precio' => 1100.00,
                'stock' => 7,
                'descripcion' => 'Blusa con pechera bordada en motivos geométricos zapotecos.',
                'imagenes' => [
                    'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=800',
                ],
            ],
            [
                'nombre' => 'Rebozo de algodón de Teotitlán',
                'categoria' => 'Rebozos',
                'tienda' => 'Textiles del Valle',
                'artesano' => 'Elena López Jiménez',
                'talla' => 'Único',
                'color' => 'Multicolor',
                'bordado' => 'Telar de cintura',
                'tela' => 'Algodón',
                'region' => 'Teotitlán del Valle',
                'precio' => 1450.00,
                'stock' => 6,
                'descripcion' => 'Rebozo tejido en telar de cintura con tintes naturales.',
                'imagenes' => [
                    'https://images.unsplash.com/photo-1544441893-675973e31985?w=800',
                ],
            ],
            [
                'nombre' => 'Rebozo de seda con deshilado de Oaxaca',
                'categoria' => 'Rebozos',
                'tienda' => 'Casa Mixteca',
                'artesano' => 'Carmen Vidal Pérez',
                'talla' => 'Único',
                'color' => 'Azul',
                'bordado' => 'Deshilado',
                'tela' => 'Seda',
                'region' => 'Oaxaca de Juárez',
                'precio' => 2800.00,
                'stock' => 3,
                'descripcion' => 'Rebozo de seda con deshilado fino, elaborado en taller de Oaxaca capital.',
                'imagenes' => [
                    'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?w=800',
                ],
            ],
            [
                'nombre' => 'Rebozo de lana de la Sierra Norte',
                'categoria' => 'Rebozos',
                'tienda' => 'Telar de Istmo',
                'artesano' => 'Rosa Hernández García',
                'talla' => 'Único',
                'color' => 'Negro',
                'bordado' => 'Telar',
                'tela' => 'Lana',
                'region' => 'Ixtlán de Juárez',
                'precio' => 1600.00,
                'stock' => 5,
                'descripcion' => 'Rebozo grueso de lana para clima de sierra, tejido a mano.',
                'imagenes' => [
                    'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=800',
                ],
            ],
            [
                'nombre' => 'Vestido bordado de gala oaxaqueño',
                'categoria' => 'Vestidos',
                'tienda' => 'Telar de Istmo',
                'artesano' => 'Juana Ruiz Morales',
                'talla' => 'M',
                'color' => 'Blanco',
                'bordado' => 'Floral',
                'tela' => 'Algodón',
                'region' => 'Juchitán de Zaragoza',
                'precio' => 4200.00,
                'stock' => 2,
                'descripcion' => 'Vestido de gala con bordado floral abundante, inspirado en el traje istmeño.',
                'imagenes' => [
                    'https://images.unsplash.com/photo-1596993100471-c3905dafa78e?w=800',
                ],
            ],
            [
                'nombre' => 'Vestido de manta con pechera',
                'categoria' => 'Vestidos',
                'tienda' => 'Textiles del Valle',
                'artesano' => 'María Sánchez Cruz',
                'talla' => 'S',
                'color' => 'Crema',
                'bordado' => 'Punto de cruz',
                'tela' => 'Manta',
                'region' => 'Ocotlán de Morelos',
                'precio' => 2100.00,
                'stock' => 6,
                'descripcion' => 'Vestido de manta con pechera bordada en punto de cruz.',
                'imagenes' => [
                    'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=800',
                ],
            ],
            [
                'nombre' => 'Camisa de manta con detalle mixteco',
                'categoria' => 'Camisas artesanales',
                'tienda' => 'Casa Mixteca',
                'artesano' => 'Elena López Jiménez',
                'talla' => 'L',
                'color' => 'Blanco',
                'bordado' => 'Geométrico',
                'tela' => 'Manta',
                'region' => 'Huajuapan de León',
                'precio' => 680.00,
                'stock' => 14,
                'descripcion' => 'Camisa unisex de manta con bordado geométrico en cuello y puños.',
                'imagenes' => [
                    'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=800',
                ],
            ],
            [
                'nombre' => 'Camisa de lino con bordado sutil',
                'categoria' => 'Camisas artesanales',
                'tienda' => 'Textiles del Valle',
                'artesano' => 'Carmen Vidal Pérez',
                'talla' => 'XL',
                'color' => 'Beige',
                'bordado' => 'Línea',
                'tela' => 'Lino',
                'region' => 'Oaxaca de Juárez',
                'precio' => 920.00,
                'stock' => 9,
                'descripcion' => 'Camisa de lino fresco con bordado sutil en el pecho.',
                'imagenes' => [
                    'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?w=800',
                ],
            ],
            [
                'nombre' => 'Camino de mesa de telar de Teotitlán',
                'categoria' => 'Textiles',
                'tienda' => 'Textiles del Valle',
                'artesano' => 'Rosa Hernández García',
                'talla' => 'Único',
                'color' => 'Multicolor',
                'bordado' => 'Telar de cintura',
                'tela' => 'Lana',
                'region' => 'Teotitlán del Valle',
                'precio' => 1350.00,
                'stock' => 8,
                'descripcion' => 'Camino de mesa tejido en telar de cintura con tintes naturales.',
                'imagenes' => [
                    'https://images.unsplash.com/photo-1528459801416-a9e53bbf4e17?w=800',
                ],
            ],
            [
                'nombre' => 'Mantel de algodón con grecas zapotecas',
                'categoria' => 'Textiles',
                'tienda' => 'Casa Mixteca',
                'artesano' => 'Juana Ruiz Morales',
                'talla' => 'Único',
                'color' => 'Rojo',
                'bordado' => 'Geométrico',
                'tela' => 'Algodón',
                'region' => 'Mitla',
                'precio' => 1750.00,
                'stock' => 4,
                'descripcion' => 'Mantel con grecas inspiradas en la iconografía de Mitla.',
                'imagenes' => [
                    'https://images.unsplash.com/photo-1544441893-675973e31985?w=800',
                ],
            ],
            [
                'nombre' => 'Servilletas bordadas set de 4',
                'categoria' => 'Textiles',
                'tienda' => 'Textiles del Valle',
                'artesano' => 'María Sánchez Cruz',
                'talla' => 'Único',
                'color' => 'Blanco',
                'bordado' => 'Floral',
                'tela' => 'Manta',
                'region' => 'San Antonino Castillo Velasco',
                'precio' => 480.00,
                'stock' => 20,
                'descripcion' => 'Set de cuatro servilletas de manta con esquina bordada a mano.',
                'imagenes' => [
                    'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=800',
                ],
            ],
        ];

        foreach ($items as $data) {
            $imagenes = $data['imagenes'];
            $catNombre = $data['categoria'];
            $tiendaNombre = $data['tienda'];
            $artesanoNombre = $data['artesano'];
            unset($data['imagenes'], $data['categoria'], $data['tienda'], $data['artesano']);

            $articulo = Articulo::updateOrCreate(
                [
                    'nombre' => $data['nombre'],
                    'region' => $data['region'],
                ],
                array_merge($data, [
                    'categoria_id' => $categorias[$catNombre],
                    'tienda_id' => $tiendas[$tiendaNombre],
                    'artesano_id' => $artesanos[$artesanoNombre],
                ])
            );

            // Reemplazar imágenes para evitar duplicados al reseeder
            ImagenArticulo::where('articulo_id', $articulo->id)->delete();
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
