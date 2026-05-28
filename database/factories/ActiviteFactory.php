<?php

namespace Database\Factories;

use App\Models\Activite;
use App\Models\Destination;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActiviteFactory extends Factory
{
    protected $model = Activite::class;

    public function definition(): array
    {
        return [
            'nom' => $this->faker->words(2, true),
            'description' => $this->faker->paragraph(),
            'prix' => $this->faker->randomFloat(2, 100, 500),
            'image' => 'default_activite.jpg',
            'adapte_enfants' => $this->faker->boolean(),
            'destination_id' => Destination::factory(),
        ];
    }
}