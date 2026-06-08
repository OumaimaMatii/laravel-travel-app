<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hotel;
use App\Models\TypeChambre;
use App\Models\HotelTypeChambre;

class HotelTypeChambreSeeder extends Seeder
{
    public function run(): void
    {
        $hotels = Hotel::all();
        $types = TypeChambre::all();
        
        if ($hotels->isEmpty() || $types->isEmpty()) {
            $this->command->warn('Aucun hotel ou type de chambre trouve');
            return;
        }
        
        foreach ($hotels as $hotel) {
            foreach ($types as $index => $type) {
                HotelTypeChambre::updateOrCreate(
                    [
                        'hotel_id' => $hotel->id,
                        'type_chambre_id' => $type->id,
                    ],
                    [
                        'quantite_disponible' => rand(5, 30),
                        'prix_par_nuit' => rand(500, 5000),
                    ]
                );
            }
        }
        
        $this->command->info('Associations hotel-type chambre creees avec succes');
    }
}