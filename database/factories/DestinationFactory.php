<?php

namespace Database\Factories;

use App\Models\Destination;
use App\Models\Ville;
use Illuminate\Database\Eloquent\Factories\Factory;

class DestinationFactory extends Factory
{
    protected $model = Destination::class;

    public function definition(): array
    {
        return [
            'nom' => $this->faker->words(2, true),
            'pays' => 'maroc',
            'image_couverture' => 'default_destination.jpg',
            'actif' => true,
            'ville_id' => Ville::factory(),
        ];
    }
}