<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Ville;
use App\Models\Destination;
use App\Models\TypeChambre;
use App\Models\Hotel;
use App\Models\Activite;
use App\Models\TypeTransport;
use App\Models\Transport;
use App\Models\TypeVoyage;
use App\Models\StatutForfait;
use App\Models\StatutSurMesure;
use App\Models\Voyage;
use App\Models\VoyageForfait;
use App\Models\VoyageSurMesure;
use App\Models\Reservation;
use App\Models\Voyageur;
use App\Models\Media;
use App\Models\HotelTypeChambre;
use App\Models\VoyageTransport;
use App\Models\VoyageActivite;
use App\Models\DetailReservation;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ========== 1. EXÉCUTION DES SEEDERS SPÉCIFIQUES ==========
        
        // ✅ Ajouter les agents et admin
        $this->call(AgentSeeder::class);
        
        // ✅ Ajouter les types de transport
        $this->call(TypeTransportSeeder::class);
        
        // ========== 2. CRÉATION DES DONNÉES DE BASE ==========

        TypeChambre::factory(5)->create();
        
        TypeVoyage::factory(2)->create();
        StatutForfait::factory(3)->create();
        StatutSurMesure::factory(5)->create();

        Ville::factory(10)->create();
        Destination::factory(20)->create();
        Hotel::factory(15)->create();
        Activite::factory(30)->create();
        Transport::factory(20)->create();
        Voyage::factory(30)->create();
        VoyageForfait::factory(15)->create();
        VoyageSurMesure::factory(10)->create();

        Reservation::factory(50)->create()->each(function ($reservation) {
            $nbPersonnes = $reservation->nb_adultes + $reservation->nb_enfants;
            Voyageur::factory($nbPersonnes)->create(['reservation_id' => $reservation->id]);
        });

        Media::factory(50)->create();

        // ========== 3. REMPLIR LES TABLES VIDES ==========

        // 3.1 hotel_type_chambre (liaison hôtels ↔ types de chambres)
        $hotels = Hotel::all();
        $typeChambres = TypeChambre::all();

        foreach ($hotels as $hotel) {
            // Chaque hôtel aura entre 2 et 4 types de chambres
            $assignedTypes = $typeChambres->random(rand(2, min(4, $typeChambres->count())));
            foreach ($assignedTypes as $type) {
                HotelTypeChambre::create([
                    'hotel_id' => $hotel->id,
                    'type_chambre_id' => $type->id,
                    'quantite_disponible' => rand(5, 30),
                    'prix_par_nuit' => rand(400, 1500),
                ]);
            }
        }

        // 3.2 voyage_transport (liaison voyages ↔ transports)
        $voyages = Voyage::all();
        $transports = Transport::all();

        foreach ($voyages as $voyage) {
            // Chaque voyage aura 1 ou 2 transports
            $assignedTransports = $transports->random(rand(1, min(2, $transports->count())));
            $ordre = 1;
            foreach ($assignedTransports as $transport) {
                VoyageTransport::create([
                    'voyage_id' => $voyage->id,
                    'transport_id' => $transport->id,
                    'ordre' => $ordre++,
                ]);
            }
        }

        // 3.3 voyage_activite (liaison voyages ↔ activités)
        $activites = Activite::all();

        foreach ($voyages as $voyage) {
            // Chaque voyage aura entre 2 et 5 activités
            $assignedActivites = $activites->random(rand(2, min(5, $activites->count())));
            foreach ($assignedActivites as $activite) {
                VoyageActivite::create([
                    'voyage_id' => $voyage->id,
                    'activite_id' => $activite->id,
                ]);
            }
        }

        // 3.4 detail_reservations (détails des réservations : chambres réservées)
        $reservations = Reservation::all();
        $hotelTypeChambres = HotelTypeChambre::all();

        foreach ($reservations as $reservation) {
            // Chaque réservation aura entre 1 et 3 types de chambres
            $nbDetails = rand(1, 3);
            $availableLinks = $hotelTypeChambres->random(min($nbDetails, $hotelTypeChambres->count()));
            
            foreach ($availableLinks as $link) {
                DetailReservation::create([
                    'reservation_id' => $reservation->id,
                    'hotel_id' => $link->hotel_id,
                    'type_chambre_id' => $link->type_chambre_id,
                    'quantite' => rand(1, 2),
                    'prix_unitaire' => $link->prix_par_nuit,
                ]);
            }
        }
    }
}