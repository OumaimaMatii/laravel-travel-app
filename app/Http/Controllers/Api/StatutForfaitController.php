<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StatutForfaitResource;
use App\Models\StatutForfait;
use Illuminate\Http\Request;

class StatutForfaitController extends Controller
{
    // GET /statut-forfait - Liste tous les statuts de forfait
    public function index()
    {
        $statuts = StatutForfait::with('voyagesForfait')->get();
        return StatutForfaitResource::collection($statuts);
    }

    // POST /statut-forfait - Crée un nouveau statut
    public function store(Request $request)
    {
        $request->validate(['nom' => 'required|string|max:255']);
        $statut = StatutForfait::create($request->all());
        return new StatutForfaitResource($statut);
    }

    // GET /statut-forfait/{id} - Affiche un statut spécifique
    public function show($id)
    {
        $statut = StatutForfait::with('voyagesForfait')->findOrFail($id);
        return new StatutForfaitResource($statut);
    }

    // PUT/PATCH /statut-forfait/{id} - Met à jour un statut
    public function update(Request $request, $id)
    {
        $statut = StatutForfait::findOrFail($id);
        $statut->update($request->all());
        return new StatutForfaitResource($statut);
    }

    // DELETE /statut-forfait/{id} - Supprime un statut
    public function destroy($id)
    {
        $statut = StatutForfait::findOrFail($id);
        $statut->delete();
        return response()->json(null, 204);
    }
}