<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transport;
use App\Models\TypeTransport;
use App\Models\Ville;
use Carbon\Carbon;

class TransportSeeder extends Seeder
{
    public function run(): void
    {
        $bus50 = TypeTransport::where('nom', 'Bus 50 places')->first();
        $bus30 = TypeTransport::where('nom', 'Bus 30 places')->first();
        $train = TypeTransport::where('nom', 'Train')->first();
        $avion = TypeTransport::where('nom', 'Avion')->first();
        
        $casablanca = Ville::where('nom', 'Casablanca')->first();
        $marrakech = Ville::where('nom', 'Marrakech')->first();
        $rabat = Ville::where('nom', 'Rabat')->first();
        $tanger = Ville::where('nom', 'Tanger')->first();
        $fes = Ville::where('nom', 'Fes')->first();
        $agadir = Ville::where('nom', 'Agadir')->first();
        
        $transports = [
            // Forfait transports (aller-retour Casablanca - Marrakech)
            [
                'compagnie' => 'CTM Voyages',
                'numero_vol' => 'FORFAIT-001-A',
                'depart' => $casablanca ? $casablanca->nom : 'Casablanca',
                'arrivee' => $marrakech ? $marrakech->nom : 'Marrakech',
                'ville_depart_id' => $casablanca ? $casablanca->id : null,
                'ville_arrivee_id' => $marrakech ? $marrakech->id : null,
                'heure_depart' => Carbon::parse('2026-07-15 08:00:00'),
                'heure_arrivee' => Carbon::parse('2026-07-15 11:00:00'),
                'prix' => 150,
                'places_disponibles' => 50,
                'type_transport_id' => $bus50 ? $bus50->id : null,
            ],
            [
                'compagnie' => 'CTM Voyages',
                'numero_vol' => 'FORFAIT-001-R',
                'depart' => $marrakech ? $marrakech->nom : 'Marrakech',
                'arrivee' => $casablanca ? $casablanca->nom : 'Casablanca',
                'ville_depart_id' => $marrakech ? $marrakech->id : null,
                'ville_arrivee_id' => $casablanca ? $casablanca->id : null,
                'heure_depart' => Carbon::parse('2026-07-20 17:00:00'),
                'heure_arrivee' => Carbon::parse('2026-07-20 20:00:00'),
                'prix' => 150,
                'places_disponibles' => 50,
                'type_transport_id' => $bus50 ? $bus50->id : null,
            ],
            // Transport Rabat - Tanger
            [
                'compagnie' => 'Supratours',
                'numero_vol' => 'FORFAIT-002-A',
                'depart' => $rabat ? $rabat->nom : 'Rabat',
                'arrivee' => $tanger ? $tanger->nom : 'Tanger',
                'ville_depart_id' => $rabat ? $rabat->id : null,
                'ville_arrivee_id' => $tanger ? $tanger->id : null,
                'heure_depart' => Carbon::parse('2026-08-01 09:00:00'),
                'heure_arrivee' => Carbon::parse('2026-08-01 13:00:00'),
                'prix' => 200,
                'places_disponibles' => 50,
                'type_transport_id' => $bus50 ? $bus50->id : null,
            ],
            [
                'compagnie' => 'Supratours',
                'numero_vol' => 'FORFAIT-002-R',
                'depart' => $tanger ? $tanger->nom : 'Tanger',
                'arrivee' => $rabat ? $rabat->nom : 'Rabat',
                'ville_depart_id' => $tanger ? $tanger->id : null,
                'ville_arrivee_id' => $rabat ? $rabat->id : null,
                'heure_depart' => Carbon::parse('2026-08-05 14:00:00'),
                'heure_arrivee' => Carbon::parse('2026-08-05 18:00:00'),
                'prix' => 200,
                'places_disponibles' => 50,
                'type_transport_id' => $bus50 ? $bus50->id : null,
            ],
            // Transport Casablanca - Agadir
            [
                'compagnie' => 'CTM',
                'numero_vol' => 'FORFAIT-003-A',
                'depart' => $casablanca ? $casablanca->nom : 'Casablanca',
                'arrivee' => $agadir ? $agadir->nom : 'Agadir',
                'ville_depart_id' => $casablanca ? $casablanca->id : null,
                'ville_arrivee_id' => $agadir ? $agadir->id : null,
                'heure_depart' => Carbon::parse('2026-08-10 07:00:00'),
                'heure_arrivee' => Carbon::parse('2026-08-10 14:00:00'),
                'prix' => 250,
                'places_disponibles' => 50,
                'type_transport_id' => $bus50 ? $bus50->id : null,
            ],
            // Transports publics pour sur-mesure
            [
                'compagnie' => 'ONCF Train',
                'numero_vol' => null,
                'depart' => $casablanca ? $casablanca->nom : 'Casablanca',
                'arrivee' => $marrakech ? $marrakech->nom : 'Marrakech',
                'ville_depart_id' => $casablanca ? $casablanca->id : null,
                'ville_arrivee_id' => $marrakech ? $marrakech->id : null,
                'heure_depart' => Carbon::parse('2026-06-10 06:00:00'),
                'heure_arrivee' => Carbon::parse('2026-06-10 09:00:00'),
                'prix' => 180,
                'places_disponibles' => 200,
                'type_transport_id' => $train ? $train->id : null,
            ],
            [
                'compagnie' => 'Royal Air Maroc',
                'numero_vol' => 'AT401',
                'depart' => 'CMN',
                'arrivee' => 'RAK',
                'ville_depart_id' => $casablanca ? $casablanca->id : null,
                'ville_arrivee_id' => $marrakech ? $marrakech->id : null,
                'heure_depart' => Carbon::parse('2026-06-10 10:00:00'),
                'heure_arrivee' => Carbon::parse('2026-06-10 10:45:00'),
                'prix' => 850,
                'places_disponibles' => 150,
                'type_transport_id' => $avion ? $avion->id : null,
            ],
        ];

        foreach ($transports as $transport) {
            Transport::updateOrCreate(
                ['numero_vol' => $transport['numero_vol']],
                $transport
            );
        }
        
        $this->command->info('Transports crees avec succes');
    }
}