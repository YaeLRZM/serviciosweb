<?php

namespace Database\Factories;

use App\Models\Detalle_Inventario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Detalle_Inventario>
 */
class DetalleInventarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'inventario_id' => \App\Models\Inventario::query()->inRandomOrder()->value('id'),
            'user_id' => \App\Models\User::query()->inRandomOrder()->value('id'),
            'venta_id' => \App\Models\Venta::query()->inRandomOrder()->value('id'),
            'tipo_movimiento' => $this->faker->randomElement(['entrada', 'salida']),
            'observaciones' => $this->faker->sentence(),
            'cantidad' => $this->faker->numberBetween(1, 100),
        ];
    }
}
