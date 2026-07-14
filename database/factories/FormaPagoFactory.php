<?php

namespace Database\Factories;

use App\Models\FormaPago;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormaPago>
 */
class FormaPagoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->word(),
        ];
    }
}
