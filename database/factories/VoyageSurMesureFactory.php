<?php

namespace Database\Factories;

use App\Models\VoyageSurMesure;
use App\Models\Voyage;
use App\Models\User;
use App\Models\StatutSurMesure;
use Illuminate\Database\Eloquent\Factories\Factory;

class VoyageSurMesureFactory extends Factory
{
    protected $model = VoyageSurMesure::class;

    public function definition(): array
    {
        return [
            'voyage_id' => Voyage::factory(),
            'budget_estime' => $this->faker->randomFloat(2, 2000, 10000),
            'client_id' => User::factory(),
            'statut_sur_mesure_id' => StatutSurMesure::factory(),
        ];
    }
}