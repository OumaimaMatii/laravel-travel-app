<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hotel;
use App\Models\Ville;

class HotelSeeder extends Seeder
{
    public function run(): void
    {
        $villes = Ville::all()->keyBy('nom');
        
        $hotels = [
            // Casablanca
            [
                'nom' => 'Hyatt Regency Casablanca',
                'adresse' => 'Place des Nations Unies',
                'etoiles' => 5,
                'image_principale' => 'hotels/hyatt_casa.jpg',
                'ville_nom' => 'Casablanca',
            ],
            [
                'nom' => 'Four Seasons Casablanca',
                'adresse' => 'Boulevard de la Corniche',
                'etoiles' => 5,
                'image_principale' => 'hotels/four_seasons_casa.jpg',
                'ville_nom' => 'Casablanca',
            ],
            // Marrakech
            [
                'nom' => 'La Mamounia',
                'adresse' => 'Avenue Bab Jdid',
                'etoiles' => 5,
                'image_principale' => 'hotels/mamounia.jpg',
                'ville_nom' => 'Marrakech',
            ],
            [
                'nom' => 'Royal Mansour Marrakech',
                'adresse' => 'Rue Abou Abbas El Sebti',
                'etoiles' => 5,
                'image_principale' => 'hotels/royal_mansour.jpg',
                'ville_nom' => 'Marrakech',
            ],
            [
                'nom' => 'Riad Kniza',
                'adresse' => 'Bab Doukkala',
                'etoiles' => 5,
                'image_principale' => 'hotels/riad_kniza.jpg',
                'ville_nom' => 'Marrakech',
            ],
            // Fes
            [
                'nom' => 'Palais Faraj Suites & Spa',
                'adresse' => 'Fes El Bali',
                'etoiles' => 5,
                'image_principale' => 'hotels/palais_faraj.jpg',
                'ville_nom' => 'Fes',
            ],
            [
                'nom' => 'Riad Fes',
                'adresse' => 'Rue Talaa',
                'etoiles' => 4,
                'image_principale' => 'hotels/riad_fes.jpg',
                'ville_nom' => 'Fes',
            ],
            // Agadir
            [
                'nom' => 'Sofitel Agadir Royal Bay',
                'adresse' => 'Boulevard du 20 Aout',
                'etoiles' => 5,
                'image_principale' => 'hotels/sofitel_agadir.jpg',
                'ville_nom' => 'Agadir',
            ],
            [
                'nom' => 'Atlantic Palace',
                'adresse' => 'Seaside',
                'etoiles' => 4,
                'image_principale' => 'hotels/atlantic_palace.jpg',
                'ville_nom' => 'Agadir',
            ],
            // Tanger
            [
                'nom' => 'El Minzah Hotel',
                'adresse' => 'Rue de la Liberte',
                'etoiles' => 5,
                'image_principale' => 'hotels/el_minzah.jpg',
                'ville_nom' => 'Tanger',
            ],
        ];

        foreach ($hotels as $hotel) {
            $ville = $villes->get($hotel['ville_nom']);
            if ($ville) {
                Hotel::updateOrCreate(
                    ['nom' => $hotel['nom']],
                    [
                        'adresse' => $hotel['adresse'],
                        'etoiles' => $hotel['etoiles'],
                        'image_principale' => $hotel['image_principale'],
                        'ville_id' => $ville->id,
                    ]
                );
            }
        }
        
        $this->command->info('Hotels crees avec succes');
    }
}