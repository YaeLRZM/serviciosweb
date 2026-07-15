<?php

namespace Database\Factories;

use App\Models\ImagenArticulo;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Articulo;
/**
 * @extends Factory<ImagenArticulo>
 */
class ImagenArticuloFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'articulo_id' => Articulo::query()->inRandomOrder()->first()->id,
            'url' => 'https://picsum.photos/200',
            'es_principal' => false,
        ];
    }
}
