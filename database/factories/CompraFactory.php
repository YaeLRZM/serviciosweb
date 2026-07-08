<?php

namespace Database\Factories;

use App\Models\Compra;
use App\Models\User;
use App\Models\Articulo;
use Illuminate\Database\Eloquent\Factories\Factory;
/**
 * @extends Factory<Compra>
 */
class CompraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => function () {
                return User::inRandomOrder()->value('id') ?? User::factory()->create()->id;
            },
            'articulo_id' => function () {
                return Articulo::inRandomOrder()->value('id') ?? Articulo::factory()->create()->id;
            },
            'cantidad' => $this->faker->numberBetween(1, 10),
            'precio_unitario' => $this->faker->randomFloat(2, 10, 100),
        ];
    }
}
