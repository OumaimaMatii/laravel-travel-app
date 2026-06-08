<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use App\Models\VoyageForfait;
use App\Models\Transport;
use App\Traits\Notifiable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReservationController extends Controller
{
    use Notifiable;

    // GET /admin/reservations - Liste toutes les réservations (admin seulement)
    public function index()
    {
        $reservations = Reservation::with([
            'user',
            'voyage.destination',
            'voyage.destination.medias',
            'detailReservations.hotel',
            'detailReservations.typeChambre',
            'voyageurs',
            'documents',
        ])->orderBy('created_at', 'desc')->get();

        return ReservationResource::collection($reservations);
    }

    // GET /client/reservations - Réservations du client connecté
    public function mesReservations(Request $request)
    {
        $user = $request->user();

        $reservations = Reservation::with([
            'voyage.destination',
            'voyage.destination.medias',
            'voyage.destination.ville',
            'documents',
        ])
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();

        return ReservationResource::collection($reservations);
    }

    // GET /client/reservations/expired - Réservations expirées du client
    public function getExpiredReservations(Request $request)
    {
        $user = $request->user();

        $expiredReservations = Reservation::with([
            'voyage.destination',
            'voyage.forfait',
            'voyage.transports',
            'documents',
        ])
        ->where('user_id', $user->id)
        ->where('statut', 'annulee')
        ->where('confirmation_deadline', '<', Carbon::now())
        ->orderBy('confirmation_deadline', 'desc')
        ->get();

        return response()->json([
            'success' => true,
            'data' => ReservationResource::collection($expiredReservations)
        ]);
    }

    // GET /client/reservations/pending - Réservations en attente du client (non expirées)
    public function getPendingReservations(Request $request)
    {
        $user = $request->user();

        $pendingReservations = Reservation::with([
            'voyage.destination',
            'voyage.destination.medias',
            'voyage.destination.ville',
            'documents',
        ])
        ->where('user_id', $user->id)
        ->whereIn('statut', ['en_attente', 'en_attente_confirmation'])
        ->where('confirmation_deadline', '>', Carbon::now())
        ->orderBy('confirmation_deadline', 'asc')
        ->get();

        return ReservationResource::collection($pendingReservations);
    }

    // GET /client/reservations/{id} - Détail complet d'une réservation (client)
    public function show($id)
    {
        $reservation = Reservation::with([
            'user',
            'voyage.destination',
            'voyage.destination.ville',
            'voyage.destination.medias',
            'voyage.transports.typeTransport',
            'voyage.activites',
            'detailReservations.hotel',
            'detailReservations.typeChambre',
            'voyageurs',
            'documents',
        ])->findOrFail($id);

        return new ReservationResource($reservation);
    }

    // GET /agent/reservations/{id} - Vue agent/admin (sans section paiement)
    public function showAgent($id)
    {
        $reservation = Reservation::with([
            'user',
            'voyage.destination',
            'voyage.destination.ville',
            'voyage.destination.medias',
            'voyage.transports.typeTransport',
            'voyage.activites',
            'detailReservations.hotel',
            'detailReservations.typeChambre',
            'voyageurs',
            'documents',
        ])->findOrFail($id);

        return (new ReservationResource($reservation))
            ->additional(['view_mode' => 'agent']);
    }

    // PUT /admin/reservations/{id} - Met à jour une réservation
    public function update(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->update($request->all());
        return new ReservationResource($reservation->fresh([
            'user', 'voyage.destination', 'detailReservations', 'voyageurs', 'documents',
        ]));
    }

    // DELETE /admin/reservations/{id} - Supprime une réservation
    public function destroy($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->delete();
        return response()->json(null, 204);
    }

    // POST /client/reservations/{id}/annuler - Annulation par le client
    public function annuler(Request $request, $id)
    {
        $user = $request->user();

        $reservation = Reservation::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($reservation->statut === 'confirmee') {
            return response()->json(['message' => 'Impossible d\'annuler une reservation confirmee.'], 400);
        }

        DB::beginTransaction();
        try {
            $totalPersonnes = $reservation->nb_adultes + $reservation->nb_enfants;

            if ($reservation->type_verification === 'forfait') {
                $forfait = VoyageForfait::where('voyage_id', $reservation->voyage_id)->first();
                if ($forfait) {
                    $forfait->increment('places_restantes', $totalPersonnes);
                }
            }

            $reservation->update(['statut' => 'annulee']);
            DB::commit();

            $this->sendNotification(
                $reservation->user_id,
                'Reservation annulee',
                "Votre reservation #{$reservation->id} a ete annulee.",
                'annulation',
                '/client/reservations'
            );

            return response()->json(['message' => 'Reservation annulee avec succes.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // POST /client/reservations/{id}/confirmer - Confirmation par le client (paiement)
    public function confirmer(Request $request, $id)
    {
        $user        = $request->user();
        $reservation = Reservation::with('voyage')->where('user_id', $user->id)->findOrFail($id);

        if ($reservation->confirmation_deadline) {
            $deadline = Carbon::parse($reservation->confirmation_deadline);
            if ($deadline->isPast()) {
                return response()->json([
                    'message' => 'Delai de confirmation depasse. Veuillez refaire une reservation.',
                ], 400);
            }
        }

        $reservation->update([
            'statut'        => 'confirmee',
            'mode_paiement' => $request->mode_paiement ?? 'carte',
            'date_paiement' => now(),
        ]);

        $this->sendNotification(
            $reservation->user_id,
            'Reservation confirmee',
            "Votre reservation #{$reservation->id} est confirmee. Bon voyage !",
            'reservation',
            "/client/reservations/{$reservation->id}"
        );

        return response()->json(['success' => true, 'message' => 'Reservation confirmee.']);
    }

    // POST /client/reservations/forfait - Création d'une réservation forfait
    public function storeForfait(Request $request)
    {
        $request->validate([
            'voyage_forfait_id'              => 'required|exists:voyages_forfait,id',
            'nb_adultes'                     => 'required|integer|min:1',
            'nb_enfants'                     => 'integer|min:0',
            'voyageurs'                      => 'required|array|min:1',
            'voyageurs.*.nom_complet'        => 'required|string|max:255',
            'voyageurs.*.date_naissance'     => 'required|date',
            'voyageurs.*.sexe'               => 'required|in:homme,femme',
            'voyageurs.*.numero_passeport'   => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $user           = $request->user();
            $forfait        = VoyageForfait::findOrFail($request->voyage_forfait_id);
            $totalPersonnes = $request->nb_adultes + ($request->nb_enfants ?? 0);

            if ($forfait->places_restantes < $totalPersonnes) {
                return response()->json(['error' => 'Plus assez de places disponibles.'], 400);
            }

            $forfait->decrement('places_restantes', $totalPersonnes);

            $reservation = Reservation::create([
                'user_id'               => $user->id,
                'voyage_id'             => $forfait->voyage_id,
                'nb_adultes'            => $request->nb_adultes,
                'nb_enfants'            => $request->nb_enfants ?? 0,
                'date_reservation'      => now(),
                'statut'                => 'en_attente',
                'montant_total'         => $forfait->getPrixTotal($request->nb_adultes, $request->nb_enfants ?? 0),
                'confirmation_deadline' => Carbon::now()->addHour(),
                'type_verification'     => 'forfait',
                'notification_envoyee'  => false,
            ]);

            foreach ($request->voyageurs as $vData) {
                $reservation->voyageurs()->create($vData);
            }

            DB::commit();

            $this->sendNotification(
                $user->id,
                'Reservation en attente - 1h pour confirmer',
                "Votre reservation #{$reservation->id} est en attente. Confirmez-la dans l'heure pour valider vos places.",
                'reservation',
                "/client/reservations/{$reservation->id}"
            );

            return new ReservationResource($reservation->load('voyageurs', 'voyage.destination'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // GET /agent/forfaits/{forfaitId}/reservations - Réservations d'un forfait donné (agent/admin)
    public function getReservationsByForfait(Request $request, $voyageForfaitId)
    {
        $user    = $request->user();
        $forfait = VoyageForfait::with('voyage')->findOrFail($voyageForfaitId);

        if ($user->role === 'agent' && $forfait->agent_id !== $user->id) {
            return response()->json(['message' => 'Non autorise.'], 403);
        }

        $reservations = Reservation::with([
            'user',
            'voyage.destination',
            'voyageurs',
            'documents',
            'detailReservations.hotel',
            'detailReservations.typeChambre',
        ])
        ->where('voyage_id', $forfait->voyage_id)
        ->orderBy('created_at', 'desc')
        ->get();

        $stats = [
            'total'                  => $reservations->count(),
            'confirmees'             => $reservations->where('statut', 'confirmee')->count(),
            'en_attente'             => $reservations->whereIn('statut', ['en_attente', 'en_attente_confirmation'])->count(),
            'annulees'               => $reservations->where('statut', 'annulee')->count(),
            'chiffre_affaire'        => $reservations->where('statut', 'confirmee')->sum(fn ($r) => (float) $r->montant_total),
        ];

        return response()->json([
            'success'      => true,
            'data'         => ReservationResource::collection($reservations),
            'stats'        => $stats,
            'forfait'      => [
                'id'              => $forfait->id,
                'titre'           => $forfait->voyage?->titre,
                'destination'     => $forfait->voyage?->destination?->nom,
                'nombre_places'   => $forfait->nombre_places,
                'places_restantes'=> $forfait->places_restantes,
            ],
        ]);
    }

    // POST /agent/reservations/{id}/confirmer-annulation-transport - Confirmation d'annulation transport (agent/admin)
    public function confirmerAnnulationTransport(Request $request, $id)
    {
        $user = $request->user();

        if (!in_array($user->role, ['agent', 'admin'])) {
            return response()->json(['message' => 'Non autorise.'], 403);
        }

        $reservation = Reservation::with('voyage.transports')->findOrFail($id);

        DB::beginTransaction();
        try {
            $transport      = $reservation->voyage->transports()->first();
            $totalPersonnes = $reservation->nb_adultes + $reservation->nb_enfants;

            if ($transport && $transport->places_reservees_temp >= $totalPersonnes) {
                $transport->decrement('places_reservees_temp', $totalPersonnes);
            }

            $reservation->update([
                'statut'               => 'annulee',
                'notification_envoyee' => true,
            ]);

            DB::commit();

            $this->sendNotification(
                $reservation->user_id,
                'Reservation annulee',
                "Votre demande sur mesure #{$reservation->id} a ete annulee suite a l'expiration du delai.",
                'info',
                '/client/reservations'
            );

            return response()->json(['success' => true, 'message' => 'Annulation confirmee.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // GET /client/forfaits/{id}/verifier-disponibilite - Vérifie la disponibilité d'un forfait
    public function verifierDisponibiliteForfait(Request $request, $forfaitId)
    {
        $forfait = VoyageForfait::with('voyage')->findOrFail($forfaitId);
        
        return response()->json([
            'success' => true,
            'forfait_id' => $forfait->id,
            'titre' => $forfait->titre,
            'places_restantes' => $forfait->places_restantes,
            'prix_adulte' => $forfait->prix_adulte,
            'prix_enfant' => $forfait->prix_enfant,
            'date_depart' => $forfait->voyage->date_depart,
            'date_retour' => $forfait->voyage->date_retour,
            'peut_reserver' => $forfait->places_restantes > 0
        ]);
    }

    // POST /client/reservations/{id}/prolonger - Prolonge une réservation forfait expirée de +1 heure
    public function prolongerReservation(Request $request, $id)
    {
        $user = $request->user();
        
        $reservation = Reservation::where('id', $id)
            ->where('user_id', $user->id)
            ->where('type_verification', 'forfait')
            ->where('statut', 'annulee')
            ->firstOrFail();
        
        $forfait = $reservation->voyage->forfait;
        
        if (!$forfait) {
            return response()->json([
                'success' => false,
                'message' => 'Forfait non trouve'
            ], 404);
        }
        
        $totalPersonnes = $reservation->nb_adultes + $reservation->nb_enfants;
        
        if ($forfait->places_restantes < $totalPersonnes) {
            return response()->json([
                'success' => false,
                'message' => "Plus assez de places. Il reste {$forfait->places_restantes} place(s) disponible(s)."
            ], 400);
        }
        
        DB::beginTransaction();
        
        try {
            $forfait->decrement('places_restantes', $totalPersonnes);
            
            $reservation->update([
                'statut' => 'en_attente',
                'confirmation_deadline' => Carbon::now()->addHour(),
                'notification_envoyee' => false
            ]);
            
            DB::commit();
            
            $this->sendNotification(
                $user->id,
                'Reservation prolongee',
                "Votre reservation #{$reservation->id} a ete prolongee d'une heure. Nouveau delai: " . Carbon::now()->addHour()->format('H:i'),
                'reservation',
                "/client/reservations/{$reservation->id}"
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Reservation prolongee d\'une heure !',
                'nouvelle_reservation_id' => $reservation->id,
                'confirmation_deadline' => $reservation->fresh()->confirmation_deadline
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    // POST /client/forfaits/{id}/refaire - Recrée une réservation forfait expirée (avec ressaisie des voyageurs)
    public function refaireReservationForfait(Request $request, $ancienneReservationId)
    {
        $user = $request->user();
        
        $ancienneReservation = Reservation::where('id', $ancienneReservationId)
            ->where('user_id', $user->id)
            ->where('type_verification', 'forfait')
            ->where('statut', 'annulee')
            ->firstOrFail();
        
        $forfait = $ancienneReservation->voyage->forfait;
        
        if (!$forfait) {
            return response()->json([
                'success' => false,
                'message' => 'Forfait non trouve'
            ], 404);
        }
        
        $totalPersonnes = $ancienneReservation->nb_adultes + $ancienneReservation->nb_enfants;
        
        if ($forfait->places_restantes < $totalPersonnes) {
            return response()->json([
                'success' => false,
                'message' => "Plus assez de places. {$forfait->places_restantes} places disponibles seulement."
            ], 400);
        }
        
        DB::beginTransaction();
        
        try {
            $forfait->decrement('places_restantes', $totalPersonnes);
            
            $request->validate([
                'voyageurs' => 'required|array|min:1',
                'voyageurs.*.nom_complet' => 'required|string|max:255',
                'voyageurs.*.date_naissance' => 'required|date',
                'voyageurs.*.sexe' => 'required|in:homme,femme',
                'voyageurs.*.numero_passeport' => 'nullable|string|max:50',
            ]);
            
            $nouvelleReservation = Reservation::create([
                'user_id' => $user->id,
                'voyage_id' => $ancienneReservation->voyage_id,
                'nb_adultes' => $ancienneReservation->nb_adultes,
                'nb_enfants' => $ancienneReservation->nb_enfants,
                'date_reservation' => now(),
                'statut' => 'en_attente',
                'montant_total' => $ancienneReservation->montant_total,
                'confirmation_deadline' => Carbon::now()->addHour(),
                'type_verification' => 'forfait',
                'notification_envoyee' => false
            ]);
            
            foreach ($request->voyageurs as $vData) {
                $nouvelleReservation->voyageurs()->create($vData);
            }
            
            DB::commit();
            
            $this->sendNotification(
                $user->id,
                'Nouvelle reservation forfait creee',
                "Votre nouvelle reservation #{$nouvelleReservation->id} a ete creee.\n" .
                "Vous avez 1 heure pour confirmer.",
                'reservation',
                "/client/reservations/{$nouvelleReservation->id}"
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Nouvelle reservation creee avec succes!',
                'nouvelle_reservation_id' => $nouvelleReservation->id,
                'confirmation_deadline' => $nouvelleReservation->confirmation_deadline
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }
}