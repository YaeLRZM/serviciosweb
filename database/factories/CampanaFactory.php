<?php

namespace Database\Factories;

use App\Models\Campana;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Campana>
 */
class CampanaFactory extends Factory
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
            'nombre' => $this->faker->word(),
            'fecha_inicio' => $this->faker->date(),
            'fecha_fin' => $this->faker->date(),
            'estado' => $this->faker->randomElement(['activa', 'inactiva']),
        ];
    }
}
