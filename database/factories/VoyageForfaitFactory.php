<?php

namespace Database\Factories;

use App\Models\VoyageForfait;
use App\Models\Voyage;
use App\Models\Hotel;
use App\Models\StatutForfait;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VoyageForfaitFactory extends Factory
{
    protected $model = VoyageForfait::class;

    public function definition(): array
    {
        $places = $this->faker->numberBetween(20, 100);
        return [
            'voyage_id' => Voyage::factory(),
            'prix_adulte' => $this->faker->randomFloat(2, 800, 3000),
            'prix_enfant' => $this->faker->optional()->randomFloat(2, 400, 1500),
            'hotel_id' => Hotel::factory(),
            'statut_forfait_id' => StatutForfait::factory(),
            'programme' => $this->faker->paragraphs(3, true),
            'nombre_places' => $places,
            'places_restantes' => $places - $this->faker->numberBetween(0, $places),
            'agent_id' => User::factory(),
        ];
    }
}