<?php

namespace Database\Factories;

use App\Models\Transport;
use App\Models\TypeTransport;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransportFactory extends Factory
{
    protected $model = Transport::class;

    public function definition(): array
    {
        return [
            'compagnie' => $this->faker->company(),
            'numero_vol' => strtoupper($this->faker->bothify('??###')),
            'depart' => $this->faker->city(),
            'arrivee' => $this->faker->city(),
            'heure_depart' => $this->faker->dateTimeBetween('+1 week', '+2 months'),
            'heure_arrivee' => $this->faker->dateTimeBetween('+1 week', '+2 months'),
            'prix' => $this->faker->randomFloat(2, 200, 2000),
            'places_disponibles' => $this->faker->numberBetween(20, 200),
            'type_transport_id' => TypeTransport::factory(),
        ];
    }
}