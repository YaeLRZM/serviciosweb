<?php

namespace Database\Factories;

use App\Models\Tienda;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tienda>
 */
class TiendaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->company(),
            'descripcion' => $this->faker->paragraph(),
            'rfc_moral' => $this->faker->regexify('[A-Z]{3}[0-9]{6}[A-Z0-9]{3}'),
        ];
    }
}
