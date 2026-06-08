<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Voyage;
use App\Models\VoyageForfait;
use App\Models\TypeVoyage;
use App\Models\Destination;
use App\Models\Ville;
use App\Models\Hotel;
use App\Models\Transport;
use App\Models\User;
use App\Models\StatutForfait;
use App\Models\TypeForfait;
use Carbon\Carbon;

class VoyageForfaitSeeder extends Seeder
{
    public function run(): void
    {
        $typeVoyage = TypeVoyage::where('nom', 'forfait')->first();
        $agent = User::where('role', 'agent')->first();
        $statutDisponible = StatutForfait::where('nom', 'Disponible')->first();
        
        if (!$typeVoyage || !$agent || !$statutDisponible) {
            $this->command->error('Donnees de base manquantes (type_voyage, agent, statut)');
            return;
        }
        
        // Forfait 1: Marrakech 5 jours
        $destinationMarrakech = Destination::where('nom', 'Marrakech la Rouge')->first();
        $villeDepartCasa = Ville::where('nom', 'Casablanca')->first();
        $hotelMamounia = Hotel::where('nom', 'La Mamounia')->first();
        $typeFamille = TypeForfait::where('nom', 'Famille')->first();
        
        $transportAller = Transport::where('numero_vol', 'FORFAIT-001-A')->first();
        $transportRetour = Transport::where('numero_vol', 'FORFAIT-001-R')->first();
        
        if ($destinationMarrakech && $villeDepartCasa && $hotelMamounia) {
            // Voyage sans titre ni description
            $voyage1 = Voyage::create([
                'date_depart' => Carbon::parse('2026-07-15'),
                'date_retour' => Carbon::parse('2026-07-20'),
                'destination_id' => $destinationMarrakech->id,
                'ville_depart_id' => $villeDepartCasa->id,
                'type_voyage_id' => $typeVoyage->id,
            ]);
            
            if ($transportAller) {
                $voyage1->transports()->attach($transportAller->id, ['ordre' => 1]);
            }
            if ($transportRetour) {
                $voyage1->transports()->attach($transportRetour->id, ['ordre' => 2]);
            }
            
            // Forfait avec titre et description
            VoyageForfait::create([
                'voyage_id' => $voyage1->id,
                'titre' => 'Marrakech en famille 5 jours',
                'description' => 'Sejour familial incluant visites guidees et activites',
                'prix_adulte' => 3500,
                'prix_enfant' => 1750,
                'hotel_id' => $hotelMamounia->id,
                'statut_forfait_id' => $statutDisponible->id,
                'programme' => 'Jour 1: Arrivee, installation. Jour 2: Visite de la medina. Jour 3: Excursion dans le desert. Jour 4: Cours de cuisine. Jour 5: Depart.',
                'nombre_places' => 50,
                'places_restantes' => 35,
                'agent_id' => $agent->id,
                'type_forfait_id' => $typeFamille ? $typeFamille->id : null,
            ]);
            
            // Attacher les activites
            $activites = \App\Models\Activite::whereIn('nom', [
                'Visite de la Jemaa el-Fna',
                'Excursion dans le desert d\'Agafay',
                'Cours de cuisine marocaine',
                'Hammam et spa traditionnel'
            ])->get();
            $voyage1->activites()->sync($activites->pluck('id')->toArray());
        }
        
        // Forfait 2: Tanger 4 jours
        $destinationTanger = Destination::where('nom', 'Tanger la Perle')->first();
        $villeDepartRabat = Ville::where('nom', 'Rabat')->first();
        $hotelMinzah = Hotel::where('nom', 'El Minzah Hotel')->first();
        $typeAventure = TypeForfait::where('nom', 'Aventure')->first();
        
        $transportAller2 = Transport::where('numero_vol', 'FORFAIT-002-A')->first();
        $transportRetour2 = Transport::where('numero_vol', 'FORFAIT-002-R')->first();
        
        if ($destinationTanger && $villeDepartRabat && $hotelMinzah) {
            $voyage2 = Voyage::create([
                'date_depart' => Carbon::parse('2026-08-01'),
                'date_retour' => Carbon::parse('2026-08-05'),
                'destination_id' => $destinationTanger->id,
                'ville_depart_id' => $villeDepartRabat->id,
                'type_voyage_id' => $typeVoyage->id,
            ]);
            
            if ($transportAller2) {
                $voyage2->transports()->attach($transportAller2->id, ['ordre' => 1]);
            }
            if ($transportRetour2) {
                $voyage2->transports()->attach($transportRetour2->id, ['ordre' => 2]);
            }
            
            VoyageForfait::create([
                'voyage_id' => $voyage2->id,
                'titre' => 'Tanger Aventure 4 jours',
                'description' => 'Sejour aventure incluant randonnees et decouvertes',
                'prix_adulte' => 2800,
                'prix_enfant' => 1400,
                'hotel_id' => $hotelMinzah->id,
                'statut_forfait_id' => $statutDisponible->id,
                'programme' => 'Jour 1: Arrivee et installation. Jour 2: Visite de la kasbah. Jour 3: Excursion aux grottes d\'Hercule. Jour 4: Depart.',
                'nombre_places' => 50,
                'places_restantes' => 28,
                'agent_id' => $agent->id,
                'type_forfait_id' => $typeAventure ? $typeAventure->id : null,
            ]);
        }
        
        // Forfait 3: Agadir 6 jours
        $destinationAgadir = Destination::where('nom', 'Agadir la Blanche')->first();
        $villeDepartCasa2 = Ville::where('nom', 'Casablanca')->first();
        $hotelSofitel = Hotel::where('nom', 'Sofitel Agadir Royal Bay')->first();
        $typePlage = TypeForfait::where('nom', 'Plage')->first();
        
        $transportAller3 = Transport::where('numero_vol', 'FORFAIT-003-A')->first();
        
        if ($destinationAgadir && $villeDepartCasa2 && $hotelSofitel) {
            $voyage3 = Voyage::create([
                'date_depart' => Carbon::parse('2026-08-10'),
                'date_retour' => Carbon::parse('2026-08-16'),
                'destination_id' => $destinationAgadir->id,
                'ville_depart_id' => $villeDepartCasa2->id,
                'type_voyage_id' => $typeVoyage->id,
            ]);
            
            if ($transportAller3) {
                $voyage3->transports()->attach($transportAller3->id, ['ordre' => 1]);
            }
            
            VoyageForfait::create([
                'voyage_id' => $voyage3->id,
                'titre' => 'Agadir Plage 6 jours',
                'description' => 'Sejour detente a la plage',
                'prix_adulte' => 4500,
                'prix_enfant' => 2250,
                'hotel_id' => $hotelSofitel->id,
                'statut_forfait_id' => $statutDisponible->id,
                'programme' => 'Jour 1-5: Detente a la plage et activites nautiques. Jour 6: Depart.',
                'nombre_places' => 50,
                'places_restantes' => 42,
                'agent_id' => $agent->id,
                'type_forfait_id' => $typePlage ? $typePlage->id : null,
            ]);
        }
        
        $this->command->info('Forfaits crees avec succes');
    }
}