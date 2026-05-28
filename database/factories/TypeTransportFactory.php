<?php

namespace Database\Factories;

use App\Models\TypeTransport;
use Illuminate\Database\Eloquent\Factories\Factory;

class TypeTransportFactory extends Factory
{
    protected $model = TypeTransport::class;

    public function definition(): array
    {
        return [
            'nom' => $this->faker->randomElement(['Avion', 'Train', 'Bus', 'Taxi', 'Bateau']),
        ];
    }
}