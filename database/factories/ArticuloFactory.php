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
        return [
            'categoria_id' => \App\Models\Categoria::query()->inRandomOrder()->value('id') ?? \App\Models\Categoria::factory(),
            'artesano_id' => \App\Models\Artesano::query()->inRandomOrder()->value('id') ?? \App\Models\Artesano::factory(),
            'tiendas_id' => \App\Models\Tienda::query()->inRandomOrder()->value('id') ?? \App\Models\Tienda::factory(),
            'nombre' => $this->faker->word(),
            'talla' => $this->faker->word(),
            'color' => $this->faker->word(),
            'bordado' => $this->faker->word(),
            'tela' => $this->faker->word(),
            'region' => $this->faker->word(),
        ];
    }
}
