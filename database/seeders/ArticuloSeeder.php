<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Punto de entrada del catálogo de prueba.
 * La data real vive en CatalogoOaxacaSeeder (prendas y textiles de Oaxaca).
 */
class ArticuloSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CatalogoOaxacaSeeder::class);
    }
}
