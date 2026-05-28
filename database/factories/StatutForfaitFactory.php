<?php

namespace Database\Factories;

use App\Models\StatutForfait;
use Illuminate\Database\Eloquent\Factories\Factory;

class StatutForfaitFactory extends Factory
{
    protected $model = StatutForfait::class;

    public function definition(): array
    {
        return [
            'nom' => $this->faker->randomElement(['disponible', 'complet', 'annule']),
        ];
    }
}