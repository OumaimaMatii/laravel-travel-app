<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            VilleSeeder::class,
            TypeVoyageSeeder::class,
            TypeTransportSeeder::class,
            TypeChambreSeeder::class,
            StatutSeeder::class,
            TypeForfaitSeeder::class,
            UserSeeder::class,
            DestinationSeeder::class,
            HotelSeeder::class,
            HotelTypeChambreSeeder::class,
            ActiviteSeeder::class,
            TransportSeeder::class,
            VoyageForfaitSeeder::class,
            VoyageSurMesureSeeder::class,
            MediaSeeder::class,
        ]);
    }
}