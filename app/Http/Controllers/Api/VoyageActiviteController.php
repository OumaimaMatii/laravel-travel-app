<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VoyageActiviteResource;
use App\Models\VoyageActivite;
use Illuminate\Http\Request;

class VoyageActiviteController extends Controller
{
    // GET /voyage-activites - Liste les associations voyage/activite
    public function index()
    {
        $items = VoyageActivite::with('voyage', 'activite')->get();
        return VoyageActiviteResource::collection($items);
    }

    // POST /voyage-activites - Crée une association voyage/activite
    public function store(Request $request)
    {
        $request->validate([
            'voyage_id' => 'required|exists:voyages,id',
            'activite_id' => 'required|exists:activites,id',
        ]);
        $item = VoyageActivite::create($request->all());
        return new VoyageActiviteResource($item);
    }

    // GET /voyage-activites/{id} - Affiche une association
    public function show($id)
    {
        $item = VoyageActivite::with('voyage', 'activite')->findOrFail($id);
        return new VoyageActiviteResource($item);
    }

    // PUT/PATCH /voyage-activites/{id} - Met à jour une association
    public function update(Request $request, $id)
    {
        $item = VoyageActivite::findOrFail($id);
        $item->update($request->all());
        return new VoyageActiviteResource($item);
    }

    // DELETE /voyage-activites/{id} - Supprime une association
    public function destroy($id)
    {
        $item = VoyageActivite::findOrFail($id);
        $item->delete();
        return response()->json(null, 204);
    }
}