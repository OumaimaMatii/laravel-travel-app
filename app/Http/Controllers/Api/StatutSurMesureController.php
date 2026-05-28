<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StatutSurMesureResource;
use App\Models\StatutSurMesure;
use Illuminate\Http\Request;

class StatutSurMesureController extends Controller
{
    // GET /statut-sur-mesure - Liste tous les statuts de voyage sur mesure
    public function index()
    {
        $statuts = StatutSurMesure::with('voyagesSurMesure')->get();
        return StatutSurMesureResource::collection($statuts);
    }

    // POST /statut-sur-mesure - Crée un nouveau statut
    public function store(Request $request)
    {
        $request->validate(['nom' => 'required|string|max:255']);
        $statut = StatutSurMesure::create($request->all());
        return new StatutSurMesureResource($statut);
    }

    // GET /statut-sur-mesure/{id} - Affiche un statut spécifique
    public function show($id)
    {
        $statut = StatutSurMesure::with('voyagesSurMesure')->findOrFail($id);
        return new StatutSurMesureResource($statut);
    }

    // PUT/PATCH /statut-sur-mesure/{id} - Met à jour un statut
    public function update(Request $request, $id)
    {
        $statut = StatutSurMesure::findOrFail($id);
        $statut->update($request->all());
        return new StatutSurMesureResource($statut);
    }

    // DELETE /statut-sur-mesure/{id} - Supprime un statut
    public function destroy($id)
    {
        $statut = StatutSurMesure::findOrFail($id);
        $statut->delete();
        return response()->json(null, 204);
    }
}