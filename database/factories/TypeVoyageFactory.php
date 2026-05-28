<?php

namespace Database\Factories;

use App\Models\TypeVoyage;
use Illuminate\Database\Eloquent\Factories\Factory;

class TypeVoyageFactory extends Factory
{
    protected $model = TypeVoyage::class;

    public function definition(): array
    {
        return [
            'nom' => $this->faker->randomElement(['forfait', 'sur_mesure']),
        ];
    }
}