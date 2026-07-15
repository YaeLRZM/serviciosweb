<?php

namespace Database\Factories;

use App\Models\Envio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Envio>
 */
class EnvioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'venta_id' => \App\Models\Venta::query()->inRandomOrder()->first()->id,
            'direccion_id' => \App\Models\Direccion::query()->inRandomOrder()->first()->id,
            'numero_guia' => $this->faker->unique()->numerify('##########'),
            'paqueteria' => $this->faker->randomElement(['DHL', 'FedEx', 'UPS', 'Estafeta']),
            'estado_envio' => $this->faker->randomElement(['En tránsito', 'Entregado', 'Pendiente']),
            'fecha_envio' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'fecha_entrega' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
        ];
    }
}
