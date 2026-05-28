<?php

namespace Database\Factories;

use App\Models\Voyage;
use App\Models\Destination;
use App\Models\TypeVoyage;
use Illuminate\Database\Eloquent\Factories\Factory;

class VoyageFactory extends Factory
{
    protected $model = Voyage::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('+1 month', '+6 months');
        $end = (clone $start)->modify('+' . $this->faker->numberBetween(3, 14) . ' days');

        return [
            'date_depart' => $start,
            'date_retour' => $end,
            'destination_id' => Destination::factory(),
            'type_voyage_id' => TypeVoyage::factory(),
        ];
    }
}