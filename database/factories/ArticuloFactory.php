<?php

namespace Database\Factories;

use App\Models\Articulo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Articulo>
 */
class ArticuloFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nombre = $this->faker->words(3, true);

        return [
            'categoria_id' => \App\Models\Categoria::query()->inRandomOrder()->value('id') ?? \App\Models\Categoria::factory(),
            'artesano_id' => \App\Models\Artesano::query()->inRandomOrder()->value('id') ?? \App\Models\Artesano::factory(),
            'tienda_id' => \App\Models\Tienda::query()->inRandomOrder()->value('id') ?? \App\Models\Tienda::factory(),
            'nombre' => $nombre,
            'descripcion' => $this->faker->sentence(12),
            'precio' => $this->faker->randomFloat(2, 150, 2500),
            'stock' => $this->faker->numberBetween(1, 40),
            'talla' => $this->faker->randomElement(['XS', 'S', 'M', 'L', 'XL', 'Única']),
            'color' => $this->faker->safeColorName(),
            'bordado' => $this->faker->word(),
            'tela' => $this->faker->word(),
            'region' => $this->faker->randomElement(['Oaxaca', 'Chiapas', 'Puebla', 'Michoacán', 'Yucatán']),
        ];
    }
}
