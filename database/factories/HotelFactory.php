<?php

namespace Database\Factories;

use App\Models\Hotel;
use App\Models\Ville;
use Illuminate\Database\Eloquent\Factories\Factory;

class HotelFactory extends Factory
{
    protected $model = Hotel::class;

    public function definition(): array
    {
        return [
            'nom' => $this->faker->company() . ' Hotel',
            'adresse' => $this->faker->address(),
            'etoiles' => $this->faker->numberBetween(1, 5),
            'image_principale' => 'default_hotel.jpg',
            'ville_id' => Ville::factory(),
            
        ];
    }
}