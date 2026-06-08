<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Destination;
use App\Models\Ville;

class DestinationSeeder extends Seeder
{
    public function run(): void
    {
        $villes = Ville::all()->keyBy('nom');
        
        $destinations = [
            [
                'nom' => 'Marrakech la Rouge',
                'pays' => 'Maroc',
                'image_couverture' => 'destinations/marrakech.jpg',
                'actif' => true,
                'ville_nom' => 'Marrakech',
            ],
            [
                'nom' => 'Casablanca',
                'pays' => 'Maroc',
                'image_couverture' => 'destinations/casablanca.jpg',
                'actif' => true,
                'ville_nom' => 'Casablanca',
            ],
            [
                'nom' => 'Fes la Spirituelle',
                'pays' => 'Maroc',
                'image_couverture' => 'destinations/fes.jpg',
                'actif' => true,
                'ville_nom' => 'Fes',
            ],
            [
                'nom' => 'Tanger la Perle',
                'pays' => 'Maroc',
                'image_couverture' => 'destinations/tanger.jpg',
                'actif' => true,
                'ville_nom' => 'Tanger',
            ],
            [
                'nom' => 'Agadir la Blanche',
                'pays' => 'Maroc',
                'image_couverture' => 'destinations/agadir.jpg',
                'actif' => true,
                'ville_nom' => 'Agadir',
            ],
            [
                'nom' => 'Essaouira la Breezy',
                'pays' => 'Maroc',
                'image_couverture' => 'destinations/essaouira.jpg',
                'actif' => true,
                'ville_nom' => 'Essaouira',
            ],
            [
                'nom' => 'Ouarzazate',
                'pays' => 'Maroc',
                'image_couverture' => 'destinations/ouarzazate.jpg',
                'actif' => true,
                'ville_nom' => 'Ouarzazate',
            ],
            [
                'nom' => 'Chefchaouen la Bleue',
                'pays' => 'Maroc',
                'image_couverture' => 'destinations/chefchaouen.jpg',
                'actif' => true,
                'ville_nom' => 'Chefchaouen',
            ],
        ];

        foreach ($destinations as $destination) {
            $ville = $villes->get($destination['ville_nom']);
            if ($ville) {
                Destination::updateOrCreate(
                    ['nom' => $destination['nom']],
                    [
                        'pays' => $destination['pays'],
                        'image_couverture' => $destination['image_couverture'],
                        'actif' => $destination['actif'],
                        'ville_id' => $ville->id,
                    ]
                );
            }
        }
        
        $this->command->info('Destinations creees avec succes');
    }
}