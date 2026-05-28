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

    // GET /client/sur-mesure/transports - Retourne les transports publics disponibles
    public function getTransportsPublics(Request $request)
    {
        $query = Transport::with('typeTransport')
            ->where(function ($q) {
                // Exclure les transports internes aux forfaits
                $q->whereNull('numero_vol')
                  ->orWhere(function ($q2) {
                      $q2->where('numero_vol', 'not like', 'FORFAIT-%')
                         ->where('numero_vol', 'not like', 'ALLER-%')
                         ->where('numero_vol', 'not like', 'RETOUR-%');
                  });
            });

        // Filtre par ville de départ
        if ($request->filled('ville_depart')) {
            $query->where('depart', 'like', '%' . $request->ville_depart . '%');
        }

        $transports = $query->orderBy('compagnie')->get();

        return response()->json([
            'success' => true,
            'data'    => $transports,
        ]);
    }

    // GET /client/sur-mesure/commission - Retourne le pourcentage de commission configuré
    public function getCommission()
    {
        return response()->json([
            'success'     => true,
            'pourcentage' => CommissionConfig::getPourcentage(),
        ]);
    }

    // POST /client/sur-mesure/calculer - Calcul de prix côté serveur
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

        // Calcul du prix de l'hôtel
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

        // Calcul du prix des activités
        if (!empty($request->activites)) {
            foreach ($request->activites as $act) {
                $activite = Activite::find($act['activite_id']);
                if ($activite) {
                    $adultes   = $act['nb_adultes'] ?? 0;
                    $enfants   = $act['nb_enfants'] ?? 0;
                    $prixEnf   = $activite->prix * 0.5; // 50% pour les enfants
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

        // Calcul du prix du transport
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

    // POST /client/sur-mesure - Crée un voyage sur mesure complet
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
            'voyageurs'         => 'nullable|array',
            'voyageurs.*.nom_complet'    => 'required_with:voyageurs|string|max:255',
            'voyageurs.*.date_naissance' => 'required_with:voyageurs|date',
            'voyageurs.*.sexe'           => 'required_with:voyageurs|in:homme,femme',
            'voyageurs.*.numero_passeport' => 'nullable|string|max:50',
        ]);

        $user = $request->user();

        DB::beginTransaction();
        try {
            // 1. Création du voyage de base
            $voyage = Voyage::create([
                'date_depart'     => $request->date_depart,
                'date_retour'     => $request->date_retour,
                'destination_id'  => $request->destination_id,
                'ville_depart_id' => $request->ville_depart_id,
                'type_voyage_id'  => 2, // Type "sur mesure"
                'titre'           => $request->titre ?? null,
                'description'     => $request->description ?? null,
            ]);

            // 2. Attachement du transport si fourni
            if ($request->filled('transport_id')) {
                $voyage->transports()->attach($request->transport_id, ['ordre' => 1]);
            }

            // 3. Attachement des activités
            if (!empty($request->activites)) {
                $activiteIds = collect($request->activites)->pluck('activite_id')->toArray();
                $voyage->activites()->sync($activiteIds);
            }

            // 4. Statut initial "en_attente"
            $statutId = \App\Models\StatutSurMesure::orderBy('id')->value('id') ?? 1;

            // 5. Calcul du budget estimé si non fourni
            $budgetEstime = $request->budget_estime;
            if (!$budgetEstime) {
                $calcul = $this->calculerMontant($request);
                $budgetEstime = $calcul['total'];
            }

            // 6. Création de l'entrée voyages_sur_mesure
            $surMesure = VoyageSurMesure::create([
                'voyage_id'              => $voyage->id,
                'budget_estime'          => $budgetEstime,
                'client_id'              => $user->id,
                'statut_sur_mesure_id'   => $statutId,
            ]);

            // 7. Création de la réservation associée
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
                'confirmation_deadline'  => Carbon::now()->addHours(24), // Délai de 24h
                'type_verification'      => 'sur_mesure',
            ]);

            // 8. Création des voyageurs
            if (!empty($request->voyageurs)) {
                foreach ($request->voyageurs as $vData) {
                    $reservation->voyageurs()->create($vData);
                }
            }

            // 9. Création des détails chambres
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

            DB::commit();

            // Notification au client
            $this->sendNotification(
                $user->id,
                'Demande sur mesure créée',
                "Votre voyage sur mesure a été enregistré. Un agent vous contactera pour finaliser les détails.",
                'reservation',
                "/client/reservations/{$reservation->id}"
            );

            // Notification aux agents
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
                'message'      => 'Voyage sur mesure créé avec succès',
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

    // Calcul interne du montant total (utilisé si budget_estime non fourni)
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
}