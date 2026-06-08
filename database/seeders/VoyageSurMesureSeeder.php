<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Voyage;
use App\Models\VoyageSurMesure;
use App\Models\TypeVoyage;
use App\Models\Destination;
use App\Models\Ville;
use App\Models\User;
use App\Models\StatutSurMesure;
use Carbon\Carbon;

class VoyageSurMesureSeeder extends Seeder
{
    public function run(): void
    {
        $typeVoyage = TypeVoyage::where('nom', 'sur_mesure')->first();
        $client = User::where('role', 'client')->first();
        $statutEnAttente = StatutSurMesure::where('nom', 'En attente')->first();
        
        if (!$typeVoyage || !$client || !$statutEnAttente) {
            $this->command->warn('Donnees de base manquantes pour les voyages sur mesure');
            return;
        }
        
        $destinationMarrakech = Destination::where('nom', 'Marrakech la Rouge')->first();
        $destinationFes = Destination::where('nom', 'Fes la Spirituelle')->first();
        $villeDepartCasa = Ville::where('nom', 'Casablanca')->first();
        
        if ($destinationMarrakech && $villeDepartCasa) {
            $voyage1 = Voyage::create([
                'date_depart' => Carbon::parse('2026-09-01'),
                'date_retour' => Carbon::parse('2026-09-08'),
                'destination_id' => $destinationMarrakech->id,
                'ville_depart_id' => $villeDepartCasa->id,
                'type_voyage_id' => $typeVoyage->id,
            ]);
            
            VoyageSurMesure::create([
                'voyage_id' => $voyage1->id,
                'budget_estime' => 8500,
                'client_id' => $client->id,
                'statut_sur_mesure_id' => $statutEnAttente->id,
            ]);
        }
        
        if ($destinationFes && $villeDepartCasa) {
            $voyage2 = Voyage::create([
                'date_depart' => Carbon::parse('2026-09-15'),
                'date_retour' => Carbon::parse('2026-09-22'),
                'destination_id' => $destinationFes->id,
                'ville_depart_id' => $villeDepartCasa->id,
                'type_voyage_id' => $typeVoyage->id,
            ]);
            
            VoyageSurMesure::create([
                'voyage_id' => $voyage2->id,
                'budget_estime' => 6200,
                'client_id' => $client->id,
                'statut_sur_mesure_id' => $statutEnAttente->id,
            ]);
        }
        
        $this->command->info('Voyages sur mesure crees avec succes');
    }
}