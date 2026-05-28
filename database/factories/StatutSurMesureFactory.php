<?php

namespace Database\Factories;

use App\Models\StatutSurMesure;
use Illuminate\Database\Eloquent\Factories\Factory;

class StatutSurMesureFactory extends Factory
{
    protected $model = StatutSurMesure::class;

    public function definition(): array
    {
        return [
            'nom' => $this->faker->randomElement(['en_attente', 'devis_envoye', 'accepte_par_client', 'confirme', 'annule']),
        ];
    }
}