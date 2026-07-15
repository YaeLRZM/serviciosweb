<?php

namespace Database\Factories;

use App\Models\Vendedor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vendedor>
 */
class VendedorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tienda_id' => \App\Models\Tienda::query()->inRandomOrder()->value('id'),
            'user_id' => \App\Models\User::query()->inRandomOrder()->value('id'),
            'codigo_ine' => $this->faker->regexify('[A-Z0-9]{13}'),
            'foto_frontal_ine_link' => $this->faker->imageUrl(640, 480, 'people', true),
            'foto_trasera_ine_link' => $this->faker->imageUrl(640, 480, 'people', true),
            'estatus' => $this->faker->randomElement(['activo', 'inactivo']),
        ];
    }
}
