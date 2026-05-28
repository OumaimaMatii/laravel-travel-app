<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        return [
            'url' => 'default_media.jpg',
            'titre' => $this->faker->optional()->sentence(),
            'ordre' => $this->faker->numberBetween(0, 10),
            'est_principale' => $this->faker->boolean(20),
            'mediable_type' => $this->faker->randomElement(['App\Models\Hotel', 'App\Models\Destination', 'App\Models\Activite']),
            'mediable_id' => 1,
        ];
    }
}