<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypeChambre;

class TypeChambreSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['nom' => 'Standard', 'capacite_max' => 2],
            ['nom' => 'Double', 'capacite_max' => 2],
            ['nom' => 'Triple', 'capacite_max' => 3],
            ['nom' => 'Quadruple', 'capacite_max' => 4],
            ['nom' => 'Suite', 'capacite_max' => 4],
            ['nom' => 'Familiale', 'capacite_max' => 6],
            ['nom' => 'Deluxe', 'capacite_max' => 2],
            ['nom' => 'Presidentielle', 'capacite_max' => 4],
        ];

        foreach ($types as $type) {
            TypeChambre::updateOrCreate(
                ['nom' => $type['nom']],
                ['capacite_max' => $type['capacite_max']]
            );
        }
        
        $this->command->info('Types de chambre crees avec succes');
    }
}