<?php

namespace Database\Factories;

use App\Models\DetalleCarrito;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetalleCarrito>
 */
class DetalleCarritoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'carrito_id' => \App\Models\Carrito::query()->inRandomOrder()->first()->id,
            'articulo_id' => \App\Models\Articulo::query()->inRandomOrder()->first()->id,
            'cantidad' => $this->faker->numberBetween(1, 10),
            'precio_unitario' => $this->faker->randomFloat(2, 1, 100),
        ];
    }
}
