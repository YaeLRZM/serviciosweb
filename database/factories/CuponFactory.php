<?php

namespace Database\Factories;

use App\Models\Cupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cupon>
 */
class CuponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tienda_id' => \App\Models\Tienda::query()->inRandomOrder()->first()->id,
            'codigo' => $this->faker->unique()->bothify('????-####'),
            'porcentaje_descuento' => $this->faker->numberBetween(5, 50),
            'limite_uso' => $this->faker->numberBetween(1, 100),
            'fecha_expiracion' => $this->faker->dateTimeBetween('now', '+1 year'),
            'compra_minima' => $this->faker->randomFloat(2, 10, 500),
        ];
    }
}
