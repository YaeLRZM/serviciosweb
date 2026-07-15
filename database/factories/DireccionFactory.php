<?php

namespace Database\Factories;

use App\Models\Direccion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Direccion>
 */
class DireccionFactory extends Factory
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
            'estado_id' => \App\Models\Estado::query()->inRandomOrder()->value('id'),
            'calle' => $this->faker->streetName(),
            'numero_exterior' => $this->faker->buildingNumber(),
            'numero_interior' => $this->faker->optional()->buildingNumber(),
            'colonia' => $this->faker->citySuffix(),
            'codigo_postal' => $this->faker->postcode(),
            'ciudad' => $this->faker->city(),
            'pais' => $this->faker->country(),
        ];
    }
}
