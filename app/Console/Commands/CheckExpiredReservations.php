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
    
    protected $signature = 'reservations:check-expired';
    protected $description = 'Verifie les reservations expirees selon leur type';

    public function handle()
    {
        $expiredReservations = Reservation::where('statut', 'en_attente')
            ->where('confirmation_deadline', '<', Carbon::now())
            ->where('notification_envoyee', false)
            ->with(['voyage', 'voyage.forfait', 'voyage.surMesure', 'user'])
            ->get();
        
        foreach ($expiredReservations as $reservation) {
            $isForfait = $reservation->voyage->forfait !== null;
            $isSurMesure = $reservation->voyage->surMesure !== null;
            
            if ($isForfait) {
                $this->traiterForfait($reservation);
            } elseif ($isSurMesure) {
                $this->traiterSurMesure($reservation);
            }
            
            $reservation->update([
                'statut' => 'annulee',
                'notification_envoyee' => true
            ]);
        }
        
        $this->info("Termine. {$expiredReservations->count()} reservation(s) traitee(s) et annulee(s).");
    }
    
    private function traiterForfait($reservation)
    {
        $totalPersonnes = $reservation->nb_adultes + $reservation->nb_enfants;
        $forfait = $reservation->voyage->forfait;
        
        if ($forfait) {
            $forfait->increment('places_restantes', $totalPersonnes);
            
            $message = "VOTRE RESERVATION FORFAIT A EXPIRE\n\n" .
                       "Reservation #{$reservation->id}\n" .
                       "Date d'expiration: " . Carbon::now()->format('d/m/Y H:i') . "\n\n" .
                       "Details:\n" .
                       "- {$totalPersonnes} place(s) ont ete liberees\n" .
                       "- Places restantes sur le forfait: {$forfait->places_restantes}\n\n" .
                       "Pour reserver a nouveau:\n" .
                       "1. Verifiez la disponibilite actuelle\n" .
                       "2. Refaites une nouvelle reservation\n\n" .
                       "Cliquez ici pour modifier votre reservation: /forfaits/{$forfait->id}/reserver";
            
            $this->sendNotification(
                $reservation->user_id,
                'Reservation forfait expiree - A modifier',
                $message,
                'rappel',
                "/forfaits/{$forfait->id}/reserver"
            );
            
            if ($forfait->agent_id) {
                $this->sendNotification(
                    $forfait->agent_id,
                    'Places liberees - Forfait expire',
                    "Une reservation pour le forfait #{$forfait->id} a expire et a ete annulee.\n" .
                    "{$totalPersonnes} place(s) sont a nouveau disponibles.\n" .
                    "Places restantes: {$forfait->places_restantes}",
                    'alerte',
                    "/agent/forfaits/{$forfait->id}"
                );
            }
            
            $this->info("Forfait #{$forfait->id}: {$totalPersonnes} place(s) liberees, reservation #{$reservation->id} annulee");
        }
    }
    
    private function traiterSurMesure($reservation)
    {
        $totalPersonnes = $reservation->nb_adultes + $reservation->nb_enfants;
        $surMesure = $reservation->voyage->surMesure;
        $transport = $reservation->voyage->transports()->first();
        
        if ($transport) {
            $transport->decrement('places_reservees_temp', $totalPersonnes);
        }
        
        $transportDisponible = false;
        $placesRestantesTransport = 0;
        
        if ($transport) {
            $placesRestantesTransport = $transport->places_disponibles - $transport->places_reservees_temp;
            $transportDisponible = $placesRestantesTransport >= $totalPersonnes;
        }
        
        $clientMessage = "VOTRE DEMANDE SUR MESURE A EXPIRE\n\n" .
                         "Demande #{$reservation->id}\n" .
                         "Date d'expiration: " . Carbon::now()->format('d/m/Y H:i') . "\n\n" .
                         "AVANT DE REFAIRE VOTRE DEMANDE, VEUILLEZ VERIFIER:\n\n" .
                         "1. DISPONIBILITE DU TRANSPORT:\n";
        
        if ($transport) {
            if ($transportDisponible) {
                $clientMessage .= "   Transport trouve: {$transport->compagnie}\n" .
                                  "   Places disponibles: {$placesRestantesTransport} (vous avez besoin de {$totalPersonnes})\n";
            } else {
                $clientMessage .= "   Transport non disponible actuellement!\n" .
                                  "   Compagnie: {$transport->compagnie}\n" .
                                  "   Places disponibles: {$placesRestantesTransport} / besoin: {$totalPersonnes}\n" .
                                  "   Suggestion: Choisissez un autre transport ou modifiez vos dates\n";
            }
        } else {
            $clientMessage .= "   Aucun transport defini pour cette demande\n" .
                              "   Veuillez selectionner un transport dans votre nouvelle demande\n";
        }
        
        $clientMessage .= "\n2. DISPONIBILITE DE L'HOTEL:\n" .
                          "   Verifiez si l'hotel a encore des chambres disponibles\n\n" .
                          "3. MODIFICATION DES DATES:\n" .
                          "   Vous pouvez modifier vos dates de voyage\n\n" .
                          "Cliquez ici pour MODIFIER et REFAIRE votre demande:\n" .
                          "   /sur-mesure/modifier/{$reservation->id}";
        
        $this->sendNotification(
            $reservation->user_id,
            'Demande sur mesure expiree - A modifier et verifier le transport',
            $clientMessage,
            'alerte',
            "/sur-mesure/modifier/{$reservation->id}"
        );
        
        $userName = $reservation->user ? $reservation->user->name : 'Client inconnu';
        
        $agentMessage = "URGENT - RESERVATION TRANSPORT A ANNULER\n\n" .
                        "RESUME:\n" .
                        "Client: {$userName}\n" .
                        "Reservation #{$reservation->id} - EXPIREE ET ANNULEE\n" .
                        "Date d'annulation: " . Carbon::now()->format('d/m/Y H:i') . "\n" .
                        "Nombre de personnes: {$totalPersonnes}\n\n" .
                        "TRANSPORT:\n" .
                        "Compagnie: " . ($transport ? "{$transport->compagnie}" : "Non defini") . "\n" .
                        "Numero: " . ($transport ? "{$transport->numero_vol}" : "N/A") . "\n" .
                        "Places temporaires liberees: {$totalPersonnes}\n" .
                        "Places restantes apres liberation: " . ($transport ? ($transport->places_disponibles - $transport->places_reservees_temp) : "N/A") . "\n\n" .
                        "ACTIONS REQUISES:\n" .
                        "1. Appeler la compagnie pour annuler la reservation temporaire\n" .
                        "2. Numero de reference: RES-{$reservation->id}\n" .
                        "3. Verifier si le client peut refaire une reservation\n\n" .
                        "Une fois annule, confirmez via le lien ci-dessous:\n" .
                        "/admin/reservations/{$reservation->id}/confirmer-annulation-transport";
        
        $adminIds = \App\Models\User::whereIn('role', ['agent', 'admin'])->pluck('id')->toArray();
        
        if (!empty($adminIds)) {
            foreach ($adminIds as $adminId) {
                $this->sendNotification(
                    $adminId,
                    'Action requise - Annuler reservation transport (expiree)',
                    $agentMessage,
                    'urgence',
                    "/admin/reservations/{$reservation->id}/confirmer-annulation-transport"
                );
            }
            $this->info("Notifications envoyees a " . count($adminIds) . " agents/admins");
        } else {
            $this->warn("Aucun agent ou admin trouve pour recevoir la notification");
        }
        
        if ($surMesure && $surMesure->agent_id) {
            $this->sendNotification(
                $surMesure->agent_id,
                'Annulation de transport requise (expiration)',
                "Le client {$userName} n'a pas confirme sa reservation sur mesure.\n" .
                "Reservation #{$reservation->id} annulee.\n" .
                "Merci d'annuler les places temporaires du transport et d'informer le client des disponibilites actuelles.",
                'urgence',
                "/agent/reservations/{$reservation->id}"
            );
        }
        
        $this->info("Sur mesure #{$reservation->id}: Annulee, Client et agents notifies, transport verifie");
    }
}