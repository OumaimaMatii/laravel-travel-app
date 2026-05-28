<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VoyageForfaitResource;
use App\Models\VoyageForfait;
use App\Models\Voyage;
use App\Models\Transport;
use App\Models\TypeTransport;
use App\Models\Ville;
use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoyageForfaitController extends Controller
{
    // GET /forfaits - Liste tous les forfaits (public)
    public function index()
    {
        $forfaits = VoyageForfait::with([
            'voyage.destination',
            'voyage.destination.medias',
            'voyage.typeVoyage',
            'voyage.activites',
            'voyage.transports',
            'voyage.transports.typeTransport',
            'voyage.transports.villeDepart',
            'voyage.transports.villeArrivee',
            'voyage.villeDepart',
            'hotel',
            'hotel.medias',
            'statut',
            'agent'
        ])->get();

        return VoyageForfaitResource::collection($forfaits);
    }

    // GET /forfaits/{id} - Affiche un forfait spécifique
    public function show($id)
    {
        $forfait = VoyageForfait::with([
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
            'hotel',
            'hotel.medias',
            'hotel.typeChambres',
            'statut',
            'agent'
        ])->findOrFail($id);

        return new VoyageForfaitResource($forfait);
    }

    // GET /agent/forfaits - Récupère les forfaits de l'agent connecté
    public function mesForfaits(Request $request)
    {
        $user = $request->user();
        
        if (!in_array($user->role, ['agent', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }
        
        $query = VoyageForfait::with([
            'voyage.destination',
            'voyage.destination.medias',
            'voyage.typeVoyage',
            'voyage.activites',
            'voyage.transports',
            'voyage.transports.typeTransport',
            'voyage.transports.villeDepart',
            'voyage.transports.villeArrivee',
            'voyage.villeDepart',
            'hotel',
            'hotel.medias',
            'statut',
            'agent'
        ]);
        
        if ($user->role === 'agent') {
            $query->where('agent_id', $user->id);
        }
        
        $forfaits = $query->orderBy('created_at', 'desc')->get();
        
        return VoyageForfaitResource::collection($forfaits);
    }

    // GET /type-transports - Récupère les types de transport pour le frontend
    public function getTypesTransport()
    {
        $types = TypeTransport::all();
        return response()->json([
            'success' => true,
            'data' => $types
        ]);
    }

    // Extrait la capacité (nombre de places) depuis le nom du bus
    private function extractCapacite($nom)
    {
        preg_match('/(\d+)\s*places?/i', $nom, $matches);
        return $matches[1] ?? 50; // Valeur par défaut 50 places
    }

    // POST /forfaits - Crée un nouveau forfait
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
            'prix_adulte' => 'required|numeric|min:0',
            'prix_enfant' => 'nullable|numeric|min:0',
            'hotel_id' => 'required|exists:hotels,id',
            'programme' => 'required|string',
            'nombre_places' => 'required|integer|min:1',
            'statut_forfait_id' => 'sometimes|exists:statut_forfait,id',
            'type_bus_aller_id' => 'required|exists:type_transports,id',
            'prix_bus_aller' => 'nullable|numeric|min:0',
            'type_bus_retour_id' => 'nullable|exists:type_transports,id',
            'prix_bus_retour' => 'nullable|numeric|min:0',
            'activites' => 'array|nullable',
            'activites.*' => 'exists:activites,id',
        ]);

        $user = $request->user();
        
        $villeDepart = Ville::find($request->ville_depart_id);
        $destination = Destination::with('ville')->find($request->destination_id);

        DB::beginTransaction();

        try {
            // 1. Création du voyage
            $voyage = Voyage::create([
                'date_depart' => $request->date_depart,
                'date_retour' => $request->date_retour,
                'destination_id' => $request->destination_id,
                'ville_depart_id' => $request->ville_depart_id,
                'type_voyage_id' => $request->type_voyage_id,
            ]);

            // 2. Création du transport ALLER
            $typeBusAller = TypeTransport::findOrFail($request->type_bus_aller_id);
            $capaciteAller = $this->extractCapacite($typeBusAller->nom);
            
            $transportAller = Transport::create([
                'compagnie' => $typeBusAller->nom,
                'numero_vol' => 'ALLER-' . $voyage->id,
                'depart' => $villeDepart ? $villeDepart->nom : null,
                'arrivee' => $destination ? $destination->nom : null,
                'ville_depart_id' => $villeDepart ? $villeDepart->id : null,
                'ville_arrivee_id' => $destination && $destination->ville ? $destination->ville->id : null,
                'heure_depart' => null,
                'heure_arrivee' => null,
                'prix' => $request->prix_bus_aller ?? 0,
                'places_disponibles' => $capaciteAller,
                'type_transport_id' => $typeBusAller->id,
            ]);

            $voyage->transports()->attach($transportAller->id, ['ordre' => 1]);

            // 3. Création du transport RETOUR
            $transportRetour = null;
            $nombrePlaces = $capaciteAller;

            if ($request->type_bus_retour_id && $request->type_bus_retour_id != $request->type_bus_aller_id) {
                // Transport retour différent de l'aller
                $typeBusRetour = TypeTransport::findOrFail($request->type_bus_retour_id);
                $capaciteRetour = $this->extractCapacite($typeBusRetour->nom);
                $nombrePlaces = min($capaciteAller, $capaciteRetour);
                
                $transportRetour = Transport::create([
                    'compagnie' => $typeBusRetour->nom,
                    'numero_vol' => 'RETOUR-' . $voyage->id,
                    'depart' => $destination ? $destination->nom : null,
                    'arrivee' => $villeDepart ? $villeDepart->nom : null,
                    'ville_depart_id' => $destination && $destination->ville ? $destination->ville->id : null,
                    'ville_arrivee_id' => $villeDepart ? $villeDepart->id : null,
                    'heure_depart' => null,
                    'heure_arrivee' => null,
                    'prix' => $request->prix_bus_retour ?? 0,
                    'places_disponibles' => $capaciteRetour,
                    'type_transport_id' => $typeBusRetour->id,
                ]);
                
                $voyage->transports()->attach($transportRetour->id, ['ordre' => 2]);
            } else {
                // Même transport pour aller et retour
                $voyage->transports()->attach($transportAller->id, ['ordre' => 2]);
            }

            // 4. Création du forfait
            $forfait = VoyageForfait::create([
                'voyage_id' => $voyage->id,
                'titre' => $request->titre,
                'description' => $request->description,
                'prix_adulte' => $request->prix_adulte,
                'prix_enfant' => $request->prix_enfant,
                'hotel_id' => $request->hotel_id,
                'statut_forfait_id' => $request->statut_forfait_id ?? 1,
                'programme' => $request->programme,
                'nombre_places' => $nombrePlaces,
                'places_restantes' => $nombrePlaces,
                'agent_id' => $user ? $user->id : null,
            ]);

            // 5. Ajout des activités
            if ($request->has('activites') && !empty($request->activites)) {
                $voyage->activites()->sync($request->activites);
            }

            DB::commit();

            // Rechargement des relations
            $forfait->load([
                'voyage.destination',
                'voyage.destination.ville',
                'voyage.villeDepart',
                'voyage.activites',
                'voyage.transports',
                'voyage.transports.typeTransport',
                'voyage.transports.villeDepart',
                'voyage.transports.villeArrivee',
                'hotel',
                'hotel.medias',
                'statut',
                'agent'
            ]);

            return new VoyageForfaitResource($forfait);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // PUT/PATCH /forfaits/{id} - Modifie un forfait existant
    public function update(Request $request, $id)
    {
        $forfait = VoyageForfait::with('voyage.transports')->findOrFail($id);
        
        $user = $request->user();
        if ($forfait->agent_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $request->validate([
            'date_depart' => 'sometimes|date',
            'date_retour' => 'sometimes|date|after:date_depart',
            'destination_id' => 'sometimes|exists:destinations,id',
            'ville_depart_id' => 'sometimes|exists:villes,id',
            'type_voyage_id' => 'sometimes|exists:type_voyages,id',
            'titre' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string',
            'prix_adulte' => 'sometimes|numeric|min:0',
            'prix_enfant' => 'sometimes|nullable|numeric|min:0',
            'hotel_id' => 'sometimes|exists:hotels,id',
            'programme' => 'sometimes|string',
            'nombre_places' => 'sometimes|integer|min:1',
            'places_restantes' => 'sometimes|integer|min:0',
            'statut_forfait_id' => 'sometimes|exists:statut_forfait,id',
            'type_bus_aller_id' => 'sometimes|exists:type_transports,id',
            'prix_bus_aller' => 'nullable|numeric|min:0',
            'type_bus_retour_id' => 'nullable|exists:type_transports,id',
            'prix_bus_retour' => 'nullable|numeric|min:0',
            'activites' => 'array|nullable',
            'activites.*' => 'exists:activites,id',
        ]);

        DB::beginTransaction();

        try {
            // Mise à jour du voyage
            $voyageData = [];
            if ($request->has('date_depart')) $voyageData['date_depart'] = $request->date_depart;
            if ($request->has('date_retour')) $voyageData['date_retour'] = $request->date_retour;
            if ($request->has('destination_id')) $voyageData['destination_id'] = $request->destination_id;
            if ($request->has('ville_depart_id')) $voyageData['ville_depart_id'] = $request->ville_depart_id;
            if ($request->has('type_voyage_id')) $voyageData['type_voyage_id'] = $request->type_voyage_id;
            
            if (!empty($voyageData)) {
                $forfait->voyage->update($voyageData);
            }

            // Mise à jour du transport aller
            $transportAller = $forfait->voyage->transports->where('pivot.ordre', 1)->first();
            
            if ($request->has('type_bus_aller_id') && $transportAller) {
                $typeBus = TypeTransport::find($request->type_bus_aller_id);
                $updateData = [
                    'compagnie' => $typeBus ? $typeBus->nom : null,
                    'type_transport_id' => $request->type_bus_aller_id,
                    'places_disponibles' => $this->extractCapacite($typeBus->nom),
                ];
                if ($request->has('prix_bus_aller')) {
                    $updateData['prix'] = $request->prix_bus_aller;
                }
                $transportAller->update($updateData);
            } elseif ($request->has('prix_bus_aller') && $transportAller) {
                $transportAller->update(['prix' => $request->prix_bus_aller]);
            }

            // Mise à jour du transport retour
            $transportRetour = $forfait->voyage->transports->where('pivot.ordre', 2)->first();
            
            if ($request->has('type_bus_retour_id') && $request->type_bus_retour_id) {
                if ($transportRetour) {
                    $typeBus = TypeTransport::find($request->type_bus_retour_id);
                    $updateData = [
                        'compagnie' => $typeBus ? $typeBus->nom : null,
                        'type_transport_id' => $request->type_bus_retour_id,
                    ];
                    if ($request->has('prix_bus_retour')) {
                        $updateData['prix'] = $request->prix_bus_retour;
                    }
                    $transportRetour->update($updateData);
                } else {
                    // Création d'un nouveau transport retour
                    $typeBusRetour = TypeTransport::find($request->type_bus_retour_id);
                    $capaciteRetour = $this->extractCapacite($typeBusRetour->nom);
                    $villeDepart = Ville::find($forfait->voyage->ville_depart_id);
                    $destination = Destination::with('ville')->find($forfait->voyage->destination_id);
                    
                    $nouveauTransport = Transport::create([
                        'compagnie' => $typeBusRetour->nom,
                        'numero_vol' => 'RETOUR-' . $forfait->voyage->id,
                        'depart' => $destination ? $destination->nom : null,
                        'arrivee' => $villeDepart ? $villeDepart->nom : null,
                        'ville_depart_id' => $destination && $destination->ville ? $destination->ville->id : null,
                        'ville_arrivee_id' => $villeDepart ? $villeDepart->id : null,
                        'heure_depart' => null,
                        'heure_arrivee' => null,
                        'prix' => $request->prix_bus_retour ?? 0,
                        'places_disponibles' => $capaciteRetour,
                        'type_transport_id' => $typeBusRetour->id,
                    ]);
                    
                    $forfait->voyage->transports()->attach($nouveauTransport->id, ['ordre' => 2]);
                }
            } elseif ($request->has('prix_bus_retour') && $transportRetour) {
                $transportRetour->update(['prix' => $request->prix_bus_retour]);
            }

            // Mise à jour du forfait
            $forfaitData = [];
            if ($request->has('titre')) $forfaitData['titre'] = $request->titre;
            if ($request->has('description')) $forfaitData['description'] = $request->description;
            if ($request->has('prix_adulte')) $forfaitData['prix_adulte'] = $request->prix_adulte;
            if ($request->has('prix_enfant')) $forfaitData['prix_enfant'] = $request->prix_enfant;
            if ($request->has('hotel_id')) $forfaitData['hotel_id'] = $request->hotel_id;
            if ($request->has('programme')) $forfaitData['programme'] = $request->programme;
            if ($request->has('nombre_places')) {
                $forfaitData['nombre_places'] = $request->nombre_places;
                if (!$request->has('places_restantes')) {
                    $forfaitData['places_restantes'] = $request->nombre_places;
                }
            }
            if ($request->has('places_restantes')) $forfaitData['places_restantes'] = $request->places_restantes;
            if ($request->has('statut_forfait_id')) $forfaitData['statut_forfait_id'] = $request->statut_forfait_id;
            
            if (!empty($forfaitData)) {
                $forfait->update($forfaitData);
            }

            // Mise à jour des activités
            if ($request->has('activites')) {
                $forfait->voyage->activites()->sync($request->activites);
            }

            DB::commit();

            // Rechargement des relations
            $forfait->load([
                'voyage.destination',
                'voyage.destination.medias',
                'voyage.villeDepart',
                'voyage.activites',
                'voyage.transports',
                'voyage.transports.typeTransport',
                'voyage.transports.villeDepart',
                'voyage.transports.villeArrivee',
                'hotel',
                'hotel.medias',
                'statut',
                'agent'
            ]);

            return new VoyageForfaitResource($forfait);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // DELETE /forfaits/{id} - Supprime un forfait
    public function destroy(Request $request, $id)
    {
        $forfait = VoyageForfait::with('voyage.transports')->findOrFail($id);
        
        $user = $request->user();
        if ($forfait->agent_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        DB::beginTransaction();
        
        try {
            // Suppression des transports associés
            foreach ($forfait->voyage->transports as $transport) {
                $transport->delete();
            }
            
            $forfait->delete();
            $forfait->voyage->delete();
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
        
        return response()->json(null, 204);
    }
}