<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ville;

class VilleSeeder extends Seeder
{
    public function run(): void
    {
        $villes = [
            ['nom' => 'Casablanca'],
            ['nom' => 'Rabat'],
            ['nom' => 'Marrakech'],
            ['nom' => 'Fes'],
            ['nom' => 'Tanger'],
            ['nom' => 'Agadir'],
            ['nom' => 'Essaouira'],
            ['nom' => 'Ouarzazate'],
            ['nom' => 'Chefchaouen'],
            ['nom' => 'Meknes'],
        ];

        foreach ($villes as $ville) {
            Ville::updateOrCreate(['nom' => $ville['nom']], $ville);
        }
        
        $this->command->info('Villes creees avec succes');
    }
}