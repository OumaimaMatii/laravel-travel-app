<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VoyageSurMesureResource;
use App\Models\Voyage;
use App\Models\VoyageSurMesure;
use App\Models\Transport;
use App\Models\Hotel;
use App\Models\HotelTypeChambre;
use App\Models\Activite;
use App\Models\CommissionConfig;
use App\Models\DetailReservation;
use App\Models\Reservation;
use App\Traits\Notifiable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SurMesureController extends Controller
{
    use Notifiable;

    public function getTransportsPublics(Request $request)
    {
        $query = Transport::with('typeTransport')
            ->where(function ($q) {
                $q->whereNull('numero_vol')
                  ->orWhere(function ($q2) {
                      $q2->where('numero_vol', 'not like', 'FORFAIT-%')
                         ->where('numero_vol', 'not like', 'ALLER-%')
                         ->where('numero_vol', 'not like', 'RETOUR-%');
                  });
            });

        if ($request->filled('ville_depart')) {
            $query->where('depart', 'like', '%' . $request->ville_depart . '%');
        }

        $transports = $query->orderBy('compagnie')->get();

        return response()->json([
            'success' => true,
            'data'    => $transports,
        ]);
    }

    public function getCommission()
    {
        return response()->json([
            'success'     => true,
            'pourcentage' => CommissionConfig::getPourcentage(),
        ]);
    }

    public function calculerPrix(Request $request)
    {
        $request->validate([
            'hotel_id'          => 'nullable|exists:hotels,id',
            'chambres'          => 'nullable|array',
            'chambres.*.type_chambre_id' => 'required_with:chambres|exists:type_chambres,id',
            'chambres.*.quantite'        => 'required_with:chambres|integer|min:1',
            'activites'         => 'nullable|array',
            'activites.*.activite_id'    => 'required_with:activites|exists:activites,id',
            'activites.*.nb_adultes'     => 'required_with:activites|integer|min:0',
            'activites.*.nb_enfants'     => 'nullable|integer|min:0',
            'transport_id'      => 'nullable|exists:transports,id',
            'nb_adultes'        => 'required|integer|min:1',
            'nb_enfants'        => 'nullable|integer|min:0',
            'date_depart'       => 'required|date',
            'date_retour'       => 'required|date|after:date_depart',
        ]);

        $nbAdultes = $request->nb_adultes;
        $nbEnfants = $request->nb_enfants ?? 0;
        $nbNuits   = Carbon::parse($request->date_depart)
                        ->diffInDays(Carbon::parse($request->date_retour));

        $prixHotel      = 0;
        $prixActivites  = 0;
        $prixTransport  = 0;
        $detailHotel    = [];
        $detailActivites = [];

        if ($request->filled('hotel_id') && !empty($request->chambres)) {
            foreach ($request->chambres as $chambre) {
                $htc = HotelTypeChambre::where('hotel_id', $request->hotel_id)
                    ->where('type_chambre_id', $chambre['type_chambre_id'])
                    ->first();

                if ($htc) {
                    $sousTotal = $htc->prix_par_nuit * $chambre['quantite'] * $nbNuits;
                    $prixHotel += $sousTotal;
                    $detailHotel[] = [
                        'type_chambre_id' => $chambre['type_chambre_id'],
                        'quantite'        => $chambre['quantite'],
                        'prix_par_nuit'   => $htc->prix_par_nuit,
                        'nb_nuits'        => $nbNuits,
                        'sous_total'      => $sousTotal,
                    ];
                }
            }
        }

        if (!empty($request->activites)) {
            foreach ($request->activites as $act) {
                $activite = Activite::find($act['activite_id']);
                if ($activite) {
                    $adultes   = $act['nb_adultes'] ?? 0;
                    $enfants   = $act['nb_enfants'] ?? 0;
                    $prixEnf   = $activite->prix * 0.5;
                    $sousTotal = ($adultes * $activite->prix) + ($enfants * $prixEnf);
                    $prixActivites += $sousTotal;
                    $detailActivites[] = [
                        'activite_id'       => $activite->id,
                        'nom'               => $activite->nom,
                        'prix_adulte'       => $activite->prix,
                        'prix_enfant'       => $prixEnf,
                        'nb_adultes'        => $adultes,
                        'nb_enfants'        => $enfants,
                        'sous_total'        => $sousTotal,
                    ];
                }
            }
        }

        if ($request->filled('transport_id')) {
            $transport = Transport::find($request->transport_id);
            if ($transport) {
                $prixTransport = $transport->prix * ($nbAdultes + $nbEnfants);
            }
        }

        $sousTotal   = $prixHotel + $prixActivites + $prixTransport;
        $commission  = CommissionConfig::getPourcentage();
        $montantComm = round($sousTotal * $commission / 100, 2);
        $total       = round($sousTotal + $montantComm, 2);

        return response()->json([
            'success' => true,
            'data' => [
                'prix_hotel'         => round($prixHotel, 2),
                'prix_activites'     => round($prixActivites, 2),
                'prix_transport'     => round($prixTransport, 2),
                'sous_total'         => round($sousTotal, 2),
                'commission_pct'     => $commission,
                'commission_montant' => $montantComm,
                'total'              => $total,
                'nb_nuits'           => $nbNuits,
                'detail_hotel'       => $detailHotel,
                'detail_activites'   => $detailActivites,
            ],
        ]);
    }

    private function calculerMontant(Request $request): array
    {
        $nbAdultes = $request->nb_adultes;
        $nbEnfants = $request->nb_enfants ?? 0;
        $nbNuits   = Carbon::parse($request->date_depart)
                        ->diffInDays(Carbon::parse($request->date_retour));

        $prixHotel     = 0;
        $prixActivites = 0;
        $prixTransport = 0;

        if (!empty($request->chambres) && $request->filled('hotel_id')) {
            foreach ($request->chambres as $chambre) {
                $htc = HotelTypeChambre::where('hotel_id', $request->hotel_id)
                    ->where('type_chambre_id', $chambre['type_chambre_id'])
                    ->first();
                if ($htc) {
                    $prixHotel += $htc->prix_par_nuit * $chambre['quantite'] * $nbNuits;
                }
            }
        }

        if (!empty($request->activites)) {
            foreach ($request->activites as $act) {
                $activite = Activite::find($act['activite_id']);
                if ($activite) {
                    $adultes = $act['nb_adultes'] ?? 0;
                    $enfants = $act['nb_enfants'] ?? 0;
                    $prixActivites += ($adultes * $activite->prix) + ($enfants * $activite->prix * 0.5);
                }
            }
        }

        if ($request->filled('transport_id')) {
            $transport = Transport::find($request->transport_id);
            if ($transport) {
                $prixTransport = $transport->prix * ($nbAdultes + $nbEnfants);
            }
        }

        $sousTotal   = $prixHotel + $prixActivites + $prixTransport;
        $commission  = CommissionConfig::getPourcentage();
        $total       = round($sousTotal * (1 + $commission / 100), 2);

        return compact('sousTotal', 'total', 'commission');
    }

    public function store(Request $request)
    {
        $request->validate([
            'destination_id'    => 'required|exists:destinations,id',
            'ville_depart_id'   => 'required|exists:villes,id',
            'date_depart'       => 'required|date|after:today',
            'date_retour'       => 'required|date|after:date_depart',
            'nb_adultes'        => 'required|integer|min:1',
            'nb_enfants'        => 'nullable|integer|min:0',
            'hotel_id'          => 'nullable|exists:hotels,id',
            'chambres'          => 'nullable|array',
            'chambres.*.type_chambre_id' => 'required_with:chambres|exists:type_chambres,id',
            'chambres.*.quantite'        => 'required_with:chambres|integer|min:1',
            'activites'         => 'nullable|array',
            'activites.*.activite_id'   => 'required_with:activites|exists:activites,id',
            'activites.*.nb_adultes'    => 'required_with:activites|integer|min:0',
            'activites.*.nb_enfants'    => 'nullable|integer|min:0',
            'transport_id'      => 'nullable|exists:transports,id',
            'budget_estime'     => 'nullable|numeric|min:0',
        ]);

        $user = $request->user();

        DB::beginTransaction();
        try {
            $voyage = Voyage::create([
                'date_depart'     => $request->date_depart,
                'date_retour'     => $request->date_retour,
                'destination_id'  => $request->destination_id,
                'ville_depart_id' => $request->ville_depart_id,
                'type_voyage_id'  => 2,
            ]);

            if ($request->filled('transport_id')) {
                $voyage->transports()->attach($request->transport_id, ['ordre' => 1]);
            }

            if (!empty($request->activites)) {
                $activiteIds = collect($request->activites)->pluck('activite_id')->toArray();
                $voyage->activites()->sync($activiteIds);
            }

            $statutId = \App\Models\StatutSurMesure::orderBy('id')->value('id') ?? 1;

            $budgetEstime = $request->budget_estime;
            if (!$budgetEstime) {
                $calcul = $this->calculerMontant($request);
                $budgetEstime = $calcul['total'];
            }

            $surMesure = VoyageSurMesure::create([
                'voyage_id'              => $voyage->id,
                'budget_estime'          => $budgetEstime,
                'client_id'              => $user->id,
                'statut_sur_mesure_id'   => $statutId,
            ]);

            $nbAdultes = $request->nb_adultes;
            $nbEnfants = $request->nb_enfants ?? 0;
            $montant   = $budgetEstime;

            $reservation = Reservation::create([
                'user_id'                => $user->id,
                'voyage_id'              => $voyage->id,
                'nb_adultes'             => $nbAdultes,
                'nb_enfants'             => $nbEnfants,
                'date_reservation'       => now(),
                'statut'                 => 'en_attente',
                'montant_total'          => $montant,
                'confirmation_deadline'  => Carbon::now()->addHours(24),
                'type_verification'      => 'sur_mesure',
                'notification_envoyee'   => false,
            ]);

            if (!empty($request->chambres) && $request->filled('hotel_id')) {
                $nbNuits = Carbon::parse($request->date_depart)
                    ->diffInDays(Carbon::parse($request->date_retour));

                foreach ($request->chambres as $chambre) {
                    $htc = HotelTypeChambre::where('hotel_id', $request->hotel_id)
                        ->where('type_chambre_id', $chambre['type_chambre_id'])
                        ->first();

                    if ($htc) {
                        DetailReservation::create([
                            'reservation_id'  => $reservation->id,
                            'hotel_id'        => $request->hotel_id,
                            'type_chambre_id' => $chambre['type_chambre_id'],
                            'quantite'        => $chambre['quantite'] * $nbNuits,
                            'prix_unitaire'   => $htc->prix_par_nuit,
                        ]);
                    }
                }
            }

            if ($request->filled('transport_id')) {
                $transport = Transport::find($request->transport_id);
                if ($transport) {
                    $totalPersonnes = $nbAdultes + $nbEnfants;
                    $transport->increment('places_reservees_temp', $totalPersonnes);
                }
            }

            DB::commit();

            $this->sendNotification(
                $user->id,
                'Demande sur mesure creee',
                "Votre voyage sur mesure a ete enregistre. Un agent vous contactera pour finaliser les details.",
                'reservation',
                "/client/reservations/{$reservation->id}"
            );

            $agents = \App\Models\User::whereIn('role', ['agent', 'admin'])->get();
            foreach ($agents as $agent) {
                $this->sendNotification(
                    $agent->id,
                    'Nouvelle demande sur mesure',
                    "Le client {$user->name} a soumis une nouvelle demande de voyage sur mesure.",
                    'info',
                    "/agent/sur-mesure/{$surMesure->id}"
                );
            }

            $surMesure->load([
                'voyage.destination',
                'voyage.villeDepart',
                'voyage.activites',
                'voyage.transports.typeTransport',
                'client',
                'statut',
            ]);

            return response()->json([
                'success'      => true,
                'message'      => 'Voyage sur mesure cree avec succes',
                'sur_mesure'   => new VoyageSurMesureResource($surMesure),
                'reservation'  => [
                    'id'            => $reservation->id,
                    'montant_total' => $reservation->montant_total,
                    'statut'        => $reservation->statut,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifierTransport(Request $request, $reservationId)
    {
        $user = $request->user();
        
        $reservation = Reservation::where('id', $reservationId)
            ->where('user_id', $user->id)
            ->where('type_verification', 'sur_mesure')
            ->firstOrFail();
        
        $transport = $reservation->voyage->transports()->first();
        
        $resultat = [
            'success' => true,
            'reservation_id' => $reservation->id,
            'transport_existe' => $transport !== null,
            'peut_refaire_reservation' => false,
            'message' => '',
            'transport_details' => null
        ];
        
        if (!$transport) {
            $resultat['message'] = 'Aucun transport n\'est associe a cette reservation. Veuillez selectionner un transport dans votre nouvelle demande.';
            $resultat['peut_refaire_reservation'] = false;
        } else {
            $totalPersonnes = $reservation->nb_adultes + $reservation->nb_enfants;
            $placesRestantes = $transport->places_disponibles - $transport->places_reservees_temp;
            
            $resultat['transport_details'] = [
                'id' => $transport->id,
                'compagnie' => $transport->compagnie,
                'numero_vol' => $transport->numero_vol,
                'depart' => $transport->depart,
                'arrivee' => $transport->arrivee,
                'places_disponibles' => $placesRestantes,
                'places_necessaires' => $totalPersonnes,
                'prix' => $transport->prix
            ];
            
            if ($placesRestantes >= $totalPersonnes) {
                $resultat['message'] = 'Transport disponible! Vous pouvez refaire votre reservation.';
                $resultat['peut_refaire_reservation'] = true;
            } else {
                $resultat['message'] = "Transport non disponible actuellement. Places disponibles: {$placesRestantes}, vous avez besoin de {$totalPersonnes}. Veuillez modifier votre demande (dates ou nombre de personnes).";
                $resultat['peut_refaire_reservation'] = false;
            }
        }
        
        return response()->json($resultat);
    }

    public function refaireReservation(Request $request, $ancienneReservationId)
    {
        $user = $request->user();
        
        $ancienneReservation = Reservation::where('id', $ancienneReservationId)
            ->where('user_id', $user->id)
            ->where('type_verification', 'sur_mesure')
            ->where('statut', 'annulee')
            ->firstOrFail();
        
        $transport = $ancienneReservation->voyage->transports()->first();
        
        if (!$transport) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun transport associe. Veuillez creer une nouvelle demande.',
                'code' => 'NO_TRANSPORT'
            ], 400);
        }
        
        $totalPersonnes = $ancienneReservation->nb_adultes + $ancienneReservation->nb_enfants;
        $placesRestantes = $transport->places_disponibles - $transport->places_reservees_temp;
        
        if ($placesRestantes < $totalPersonnes) {
            return response()->json([
                'success' => false,
                'message' => "Transport non disponible. {$placesRestantes} places disponibles seulement.",
                'code' => 'TRANSPORT_NOT_AVAILABLE',
                'places_disponibles' => $placesRestantes,
                'places_necessaires' => $totalPersonnes
            ], 400);
        }
        
        DB::beginTransaction();
        
        try {
            $transport->increment('places_reservees_temp', $totalPersonnes);
            
            $nouvelleReservation = Reservation::create([
                'user_id' => $user->id,
                'voyage_id' => $ancienneReservation->voyage_id,
                'nb_adultes' => $ancienneReservation->nb_adultes,
                'nb_enfants' => $ancienneReservation->nb_enfants,
                'date_reservation' => now(),
                'statut' => 'en_attente',
                'montant_total' => $ancienneReservation->montant_total,
                'confirmation_deadline' => Carbon::now()->addHours(24),
                'type_verification' => 'sur_mesure',
                'notification_envoyee' => false
            ]);
            
            foreach ($ancienneReservation->voyageurs as $voyageur) {
                $nouvelleReservation->voyageurs()->create([
                    'nom_complet' => $voyageur->nom_complet,
                    'date_naissance' => $voyageur->date_naissance,
                    'sexe' => $voyageur->sexe,
                    'numero_passeport' => $voyageur->numero_passeport
                ]);
            }
            
            DB::commit();
            
            $this->sendNotification(
                $user->id,
                'Nouvelle demande sur mesure creee',
                "Votre nouvelle demande #{$nouvelleReservation->id} a ete creee.\n" .
                "Transport verifie et disponible!\n" .
                "Vous avez jusqu'au " . Carbon::now()->addHours(24)->format('d/m/Y H:i') . " pour confirmer.",
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
                'message' => 'Erreur lors de la creation: ' . $e->getMessage()
            ], 500);
        }
    }
}