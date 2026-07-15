<?php

namespace Database\Factories;

use App\Models\Inventario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inventario>
 */
class InventarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'articulo_id' => \App\Models\Articulo::query()->inRandomOrder()->value('id'),
            'stock_actual' => $this->faker->numberBetween(0, 100),
            'stock_minimo' => $this->faker->numberBetween(0, 10),
        ];
    }
}
