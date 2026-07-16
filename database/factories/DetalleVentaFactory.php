<?php

namespace Database\Factories;

use App\Models\DetalleVenta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetalleVenta>
 */
class DetalleVentaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'venta_id' => \App\Models\Venta::query()->inRandomOrder()->value('id'),
            'articulo_id' => \App\Models\Articulo::query()->inRandomOrder()->value('id'),
            'cantidad' => $this->faker->numberBetween(1, 10),
            'precio_unitario' => $this->faker->randomFloat(2, 1, 100),
            'subtotal' => $this->faker->randomFloat(2, 1, 1000),
        ];
    }
}
