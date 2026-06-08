<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reservation;
use App\Models\User;
use App\Models\VoyageForfait;
use App\Models\Voyageur;
use Carbon\Carbon;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $client = User::where('role', 'client')->first();
        $forfait = VoyageForfait::first();
        
        if ($forfait && $client) {
            $reservation = Reservation::create([
                'user_id' => $client->id,
                'voyage_id' => $forfait->voyage_id,
                'nb_adultes' => 2,
                'nb_enfants' => 1,
                'date_reservation' => Carbon::now(),
                'statut' => 'en_attente_confirmation',
                'montant_total' => $forfait->getPrixTotal(2, 1),
                'confirmation_deadline' => Carbon::now()->addHour(),
                'notification_envoyee' => false,
                'type_verification' => 'forfait',
            ]);
            
            // Ajouter des voyageurs
            $voyageurs = [
                ['nom_complet' => 'Jean Dupont', 'date_naissance' => '1980-05-15', 'sexe' => 'homme', 'numero_passeport' => 'AA123456'],
                ['nom_complet' => 'Marie Dupont', 'date_naissance' => '1982-08-22', 'sexe' => 'femme', 'numero_passeport' => 'AA123457'],
                ['nom_complet' => 'Lucas Dupont', 'date_naissance' => '2010-03-10', 'sexe' => 'homme', 'numero_passeport' => 'AA123458'],
            ];
            
            foreach ($voyageurs as $voyageur) {
                Voyageur::create(array_merge($voyageur, ['reservation_id' => $reservation->id]));
            }
        }
    }
}