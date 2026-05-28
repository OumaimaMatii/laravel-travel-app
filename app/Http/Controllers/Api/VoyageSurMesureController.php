<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VoyageSurMesureResource;
use App\Models\VoyageSurMesure;
use App\Models\Voyage;
use App\Models\CommissionConfig;
use App\Models\ActiviteReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoyageSurMesureController extends Controller
{
    // GET /sur-mesures - Liste tous les voyages sur mesure
    public function index()
    {
        $surMesures = VoyageSurMesure::with([
            'voyage.destination',
            'voyage.destination.medias',
            'voyage.typeVoyage',
            'voyage.activites',
            'voyage.transports',
            'voyage.transports.typeTransport',
            'voyage.transports.villeDepart',
            'voyage.transports.villeArrivee',
            'voyage.villeDepart',
            'client',
            'statut'
        ])->get();

        return VoyageSurMesureResource::collection($surMesures);
    }

    // GET /sur-mesures/{id} - Affiche un voyage sur mesure spécifique
    public function show($id)
    {
        $surMesure = VoyageSurMesure::with([
            'voyage.destination',
            'voyage.destination.ville',
            'voyage.destination.medias',
            'voyage.typeVoyage',
            'voyage.activites',
            'voyage.activites.medias',
            'voyage.transports',
            'voyage.transports.typeTransport',
            'voyage.transports.villeDepart',
            'voyage.transports.villeArrivee',
            'voyage.villeDepart',
            'client',
            'statut'
        ])->findOrFail($id);

        return new VoyageSurMesureResource($surMesure);
    }

    // GET /sur-mesures/commission - Récupère le pourcentage de commission
    public function getCommission()
    {
        $commission = CommissionConfig::where('type', 'sur_mesure')->first();
        $pourcentage = $commission ? $commission->pourcentage : 15;
        
        return response()->json([
            'success' => true,
            'pourcentage' => $pourcentage
        ]);
    }

    // POST /sur-mesures - Crée un voyage sur mesure
    public function store(Request $request)
    {
        $request->validate([
            'date_depart' => 'required|date',
            'date_retour' => 'required|date|after:date_depart',
            'destination_id' => 'required|exists:destinations,id',
            'ville_depart_id' => 'required|exists:villes,id',
            'type_voyage_id' => 'required|exists:type_voyages,id',
            'titre' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'budget_estime' => 'required|numeric|min:0',
            'statut_sur_mesure_id' => 'required|exists:statut_sur_mesure,id',
            'activites' => 'array',
            'activites.*.activite_id' => 'exists:activites,id',
            'activites.*.nb_adultes' => 'integer|min:0',
            'activites.*.nb_enfants' => 'integer|min:0',
        ]);

        $user = $request->user();

        DB::beginTransaction();

        try {
            // Création du voyage
            $voyage = Voyage::create([
                'date_depart' => $request->date_depart,
                'date_retour' => $request->date_retour,
                'destination_id' => $request->destination_id,
                'ville_depart_id' => $request->ville_depart_id,
                'type_voyage_id' => $request->type_voyage_id,
                'titre' => $request->titre,
                'description' => $request->description,
            ]);

            // Création de l'entrée sur mesure
            $surMesure = VoyageSurMesure::create([
                'voyage_id' => $voyage->id,
                'budget_estime' => $request->budget_estime,
                'client_id' => $user ? $user->id : null,
                'statut_sur_mesure_id' => $request->statut_sur_mesure_id,
            ]);

            // Création des réservations d'activités
            if ($request->has('activites')) {
                foreach ($request->activites as $activite) {
                    $activiteModel = \App\Models\Activite::find($activite['activite_id']);
                    
                    ActiviteReservation::create([
                        'reservation_id' => null,
                        'activite_id' => $activite['activite_id'],
                        'nb_adultes' => $activite['nb_adultes'],
                        'nb_enfants' => $activite['nb_enfants'],
                        'prix_unitaire_adulte' => $activiteModel ? $activiteModel->prix : 0,
                        'prix_unitaire_enfant' => $activiteModel ? $activiteModel->prix * 0.5 : 0,
                    ]);
                }
            }

            DB::commit();

            $surMesure->load('voyage.destination', 'voyage.villeDepart', 'client', 'statut');

            return new VoyageSurMesureResource($surMesure);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // PUT/PATCH /sur-mesures/{id} - Modifie un voyage sur mesure
    public function update(Request $request, $id)
    {
        $surMesure = VoyageSurMesure::with('voyage')->findOrFail($id);

        $request->validate([
            'date_depart' => 'sometimes|date',
            'date_retour' => 'sometimes|date|after:date_depart',
            'destination_id' => 'sometimes|exists:destinations,id',
            'ville_depart_id' => 'sometimes|exists:villes,id',
            'type_voyage_id' => 'sometimes|exists:type_voyages,id',
            'titre' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string',
            'budget_estime' => 'sometimes|numeric|min:0',
            'statut_sur_mesure_id' => 'sometimes|exists:statut_sur_mesure,id',
        ]);

        // Mise à jour du voyage
        $voyageData = [];
        if ($request->has('date_depart')) $voyageData['date_depart'] = $request->date_depart;
        if ($request->has('date_retour')) $voyageData['date_retour'] = $request->date_retour;
        if ($request->has('destination_id')) $voyageData['destination_id'] = $request->destination_id;
        if ($request->has('ville_depart_id')) $voyageData['ville_depart_id'] = $request->ville_depart_id;
        if ($request->has('type_voyage_id')) $voyageData['type_voyage_id'] = $request->type_voyage_id;
        if ($request->has('titre')) $voyageData['titre'] = $request->titre;
        if ($request->has('description')) $voyageData['description'] = $request->description;
        
        if (!empty($voyageData)) {
            $surMesure->voyage->update($voyageData);
        }

        // Mise à jour du sur mesure
        $surMesureData = [];
        if ($request->has('budget_estime')) $surMesureData['budget_estime'] = $request->budget_estime;
        if ($request->has('statut_sur_mesure_id')) $surMesureData['statut_sur_mesure_id'] = $request->statut_sur_mesure_id;
        
        if (!empty($surMesureData)) {
            $surMesure->update($surMesureData);
        }

        $surMesure->load('voyage.destination', 'voyage.villeDepart', 'client', 'statut');

        return new VoyageSurMesureResource($surMesure);
    }

    // DELETE /sur-mesures/{id} - Supprime un voyage sur mesure
    public function destroy($id)
    {
        $surMesure = VoyageSurMesure::with('voyage')->findOrFail($id);
        
        $surMesure->delete();
        $surMesure->voyage->delete();
        
        return response()->json(null, 204);
    }

    // GET /client/sur-mesure - Demandes du client connecté
    public function mesDemandes(Request $request)
    {
        $user = $request->user();
        
        $surMesures = VoyageSurMesure::where('client_id', $user->id)
            ->with([
                'voyage.destination',
                'voyage.villeDepart',
                'statut'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return VoyageSurMesureResource::collection($surMesures);
    }
}