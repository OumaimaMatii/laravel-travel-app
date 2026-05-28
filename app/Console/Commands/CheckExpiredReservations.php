<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use App\Models\Transport;
use App\Traits\Notifiable;
use Carbon\Carbon;

class CheckExpiredReservations extends Command
{
    use Notifiable;
    
    // Commande Artisan pour vérifier les réservations expirées
    // Exécuter avec: php artisan reservations:check-expired
    protected $signature = 'reservations:check-expired';
    protected $description = 'Vérifie les réservations expirées selon leur type';

    public function handle()
    {
        // Récupère les réservations expirées non notifiées
        $expiredReservations = Reservation::where('statut', 'en_attente_confirmation')
            ->where('confirmation_deadline', '<', Carbon::now())
            ->where('notification_envoyee', false)
            ->with(['voyage', 'voyage.forfait', 'voyage.surMesure'])
            ->get();
        
        // Traite chaque réservation expirée
        foreach ($expiredReservations as $reservation) {
            $typeVoyage = $reservation->voyage->typeVoyage->nom ?? 'forfait';
            
            if ($typeVoyage === 'forfait') {
                $this->traiterForfait($reservation);
            } else {
                $this->traiterSurMesure($reservation);
            }
            
            // Marque la notification comme envoyée
            $reservation->update(['notification_envoyee' => true]);
        }
        
        $this->info("Terminé. {$expiredReservations->count()} réservation(s) traitée(s).");
    }
    
    // Traite l'expiration d'une réservation forfait
    // Libère les places et notifie le client
    private function traiterForfait($reservation)
    {
        $totalPersonnes = $reservation->nb_adultes + $reservation->nb_enfants;
        $forfait = $reservation->voyage->forfait;
        
        if ($forfait) {
            // Libère les places dans le forfait
            $forfait->increment('places_restantes', $totalPersonnes);
            
            $message = "Votre réservation #{$reservation->id} pour le forfait a expiré.\n\n" .
                       "{$totalPersonnes} place(s) ont été libérées.\n" .
                       "Places restantes: {$forfait->places_restantes}\n\n" .
                       "Vous pouvez faire une nouvelle réservation si vous le souhaitez.";
            
            $this->sendNotification(
                $reservation->user_id,
                'Réservation forfait expirée - Places libérées',
                $message,
                'rappel',
                "/forfaits/{$forfait->id}"
            );
            
            $this->info("Forfait #{$forfait->id}: {$totalPersonnes} place(s) libérées");
        }
    }
    
    // Traite l'expiration d'une réservation sur mesure
    // Libère les places temporaires du transport et notifie le client et les agents
    private function traiterSurMesure($reservation)
    {
        $totalPersonnes = $reservation->nb_adultes + $reservation->nb_enfants;
        $surMesure = $reservation->voyage->surMesure;
        $transport = $reservation->voyage->transports()->first();
        
        // Libère les places temporaires du transport
        if ($transport) {
            $transport->decrement('places_reservees_temp', $totalPersonnes);
        }
        
        // Message pour le client
        $clientMessage = "Votre demande de voyage sur mesure #{$reservation->id} a expiré.\n\n" .
                         "Pour valider votre voyage, vous devez:\n" .
                         "Revoir les conditions de votre voyage\n" .
                         "Vérifier la disponibilité des transports\n" .
                         "Refaire une nouvelle demande\n\n" .
                         "Cliquez ici pour modifier votre demande: /sur-mesure/modifier";
        
        $this->sendNotification(
            $reservation->user_id,
            'Demande sur mesure expirée',
            $clientMessage,
            'alerte',
            "/sur-mesure/modifier/{$reservation->id}"
        );
        
        // Message pour l'agent (action urgente requise)
        $agentMessage = "URGENT - Réservation transport à annuler\n\n" .
                        "Client: {$reservation->user->name}\n" .
                        "Réservation #{$reservation->id}\n" .
                        "Transport: " . ($transport ? "{$transport->compagnie} ({$transport->numero_vol})" : "Non défini") . "\n" .
                        "Nombre de personnes: {$totalPersonnes}\n\n" .
                        "ACTION REQUISE: Appeler la compagnie pour annuler la réservation temporaire.\n" .
                        "Numéro de référence: RES-{$reservation->id}\n\n" .
                        "Une fois annulé, cliquez sur le lien pour confirmer l'annulation.";
        
        // Envoie la notification à tous les agents et administrateurs
        $users = \App\Models\User::whereIn('role', ['agent', 'admin'])->get();
        foreach ($users as $user) {
            $this->sendNotification(
                $user->id,
                'Action requise - Annuler réservation transport',
                $agentMessage,
                'urgence',
                "/admin/reservations/{$reservation->id}/confirmer-annulation-transport"
            );
        }
        
        $this->info("Sur mesure #{$reservation->id}: Client et agents notifiés");
    }
}