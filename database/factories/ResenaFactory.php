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
            'articulo_id' => \App\Models\Articulo::factory(),
            'user_id' => \App\Models\User::factory(),
            'calificacion' => $this->faker->numberBetween(1, 5),
            'comentario' => $this->faker->paragraph(),
        ];
    }
}
