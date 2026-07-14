<?php

namespace Database\Factories;

use App\Models\Detalle_Campana;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Detalle_Campana>
 */
class DetalleCampanaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'campana_id' => \App\Models\Campana::query()->inRandomOrder()->first()->id,
            'articulo_id' => \App\Models\Articulo::query()->inRandomOrder()->first()->id,
            'categoria_id' => \App\Models\Categoria::query()->inRandomOrder()->first()->id,
            'porcentaje_descuento' => $this->faker->numberBetween(0, 80),
            'precio_fijo_oferta' => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
