<?php

namespace Database\Factories;

use App\Models\CuponCanjeado;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CuponCanjeado>
 */
class CuponCanjeadoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cupon_id' => \App\Models\Cupon::query()->inRandomOrder()->first()->id,
            'user_id' => \App\Models\User::query()->inRandomOrder()->first()->id,
            'venta_id' => \App\Models\Venta::query()->inRandomOrder()->first()->id,
            'monto_descuento' => $this->faker->randomFloat(2, 1, 100),
            'fecha_canje' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
