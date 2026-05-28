<?php

namespace Database\Factories;

use App\Models\Voyageur;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

class VoyageurFactory extends Factory
{
    protected $model = Voyageur::class;

    public function definition(): array
    {
        return [
            'reservation_id' => Reservation::factory(),
            'nom_complet' => $this->faker->name(),
            'date_naissance' => $this->faker->dateTimeBetween('-50 years', '-5 years'),
            'sexe' => $this->faker->randomElement(['homme', 'femme']),
            'numero_passeport' => $this->faker->optional()->bothify('??####??'),
        ];
    }
}