<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\User;
use App\Models\Voyage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'voyage_id' => Voyage::factory(),
            'nb_adultes' => $this->faker->numberBetween(1, 4),
            'nb_enfants' => $this->faker->numberBetween(0, 3),
            'date_reservation' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'statut' => $this->faker->randomElement(['en_attente', 'confirmee', 'annulee']),
        ];
    }
}