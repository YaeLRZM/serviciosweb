<?php

namespace Database\Factories;

use App\Models\Resena;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Resena>
 */
class ResenaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titulo' => $this->faker->sentence(),
            'contenido' => $this->faker->paragraph(),
            'puntuacion' => $this->faker->numberBetween(1, 5),
        ];
    }
}
