<?php

namespace Database\Factories;

use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Venta>
 */
class VentaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::query()->inRandomOrder()->value('id'),
            'forma_pago_id' => \App\Models\FormaPago::query()->inRandomOrder()->value('id'),
            'tienda_id' => \App\Models\Tienda::query()->inRandomOrder()->value('id'),
            'total' => $this->faker->randomFloat(2, 10, 1000),
            'estado' => $this->faker->randomElement(['pendiente', 'entregado', 'cancelada']),
        ];
    }
}
