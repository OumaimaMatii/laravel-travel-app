<?php

namespace Database\Factories;

use App\Models\TypeChambre;
use Illuminate\Database\Eloquent\Factories\Factory;

class TypeChambreFactory extends Factory
{
    protected $model = TypeChambre::class;

    public function definition(): array
    {
        return [
            'nom' => $this->faker->randomElement(['Single', 'Double', 'Triple', 'Familiale', 'Suite']),
            'capacite_max' => $this->faker->numberBetween(1, 6),
        ];
    }
}