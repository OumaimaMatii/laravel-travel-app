<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activite;
use App\Models\Destination;

class ActiviteSeeder extends Seeder
{
    public function run(): void
    {
        $destinations = Destination::all()->keyBy('nom');
        
        $activites = [
            // Marrakech
            [
                'nom' => 'Visite de la Jemaa el-Fna',
                'description' => 'Decouverte de la celebre place animee',
                'prix' => 150,
                'image' => 'activites/jemaa_el_fna.jpg',
                'adapte_enfants' => true,
                'destination_nom' => 'Marrakech la Rouge',
            ],
            [
                'nom' => 'Excursion dans le desert d\'Agafay',
                'description' => 'Quad et dromadaire dans le desert',
                'prix' => 450,
                'image' => 'activites/desert_agafay.jpg',
                'adapte_enfants' => false,
                'destination_nom' => 'Marrakech la Rouge',
            ],
            [
                'nom' => 'Cours de cuisine marocaine',
                'description' => 'Apprenez a preparer un tajine',
                'prix' => 350,
                'image' => 'activites/cuisine.jpg',
                'adapte_enfants' => true,
                'destination_nom' => 'Marrakech la Rouge',
            ],
            [
                'nom' => 'Hammam et spa traditionnel',
                'description' => 'Soins relaxants',
                'prix' => 280,
                'image' => 'activites/hammam.jpg',
                'adapte_enfants' => false,
                'destination_nom' => 'Marrakech la Rouge',
            ],
            // Casablanca
            [
                'nom' => 'Visite de la Mosquee Hassan II',
                'description' => 'Decouverte du plus haut minaret du monde',
                'prix' => 120,
                'image' => 'activites/hassan2.jpg',
                'adapte_enfants' => true,
                'destination_nom' => 'Casablanca',
            ],
            // Fes
            [
                'nom' => 'Visite des tannieres',
                'description' => 'Decouverte du cuir traditionnel',
                'prix' => 100,
                'image' => 'activites/tanneries.jpg',
                'adapte_enfants' => false,
                'destination_nom' => 'Fes la Spirituelle',
            ],
            [
                'nom' => 'Atelier de poterie',
                'description' => 'Creation de vos propres souvenirs',
                'prix' => 200,
                'image' => 'activites/poterie.jpg',
                'adapte_enfants' => true,
                'destination_nom' => 'Fes la Spirituelle',
            ],
            // Agadir
            [
                'nom' => 'Surf a Taghazout',
                'description' => 'Cours de surf',
                'prix' => 400,
                'image' => 'activites/surf.jpg',
                'adapte_enfants' => true,
                'destination_nom' => 'Agadir la Blanche',
            ],
            [
                'nom' => 'Excursion en bateau',
                'description' => 'Observation des dauphins',
                'prix' => 300,
                'image' => 'activites/bateau.jpg',
                'adapte_enfants' => true,
                'destination_nom' => 'Agadir la Blanche',
            ],
        ];

        foreach ($activites as $activite) {
            $destination = $destinations->get($activite['destination_nom']);
            if ($destination) {
                Activite::updateOrCreate(
                    ['nom' => $activite['nom']],
                    [
                        'description' => $activite['description'],
                        'prix' => $activite['prix'],
                        'image' => $activite['image'],
                        'adapte_enfants' => $activite['adapte_enfants'],
                        'destination_id' => $destination->id,
                    ]
                );
            }
        }
        
        $this->command->info('Activites creees avec succes');
    }
}