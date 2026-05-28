<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VilleResource;
use App\Models\Ville;
use Illuminate\Http\Request;

class VilleController extends Controller
{
    // GET /villes - Liste toutes les villes
    public function index()
    {
        $villes = Ville::with('destinations', 'hotels')->get();
        return VilleResource::collection($villes);
    }

    // POST /villes - Crée une ville
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
        ]);
        $ville = Ville::create($request->all());
        return new VilleResource($ville);
    }

    // GET /villes/{id} - Affiche une ville
    public function show($id)
    {
        $ville = Ville::with('destinations', 'hotels')->findOrFail($id);
        return new VilleResource($ville);
    }

    // PUT/PATCH /villes/{id} - Met à jour une ville
    public function update(Request $request, $id)
    {
        $ville = Ville::findOrFail($id);
        $ville->update($request->all());
        return new VilleResource($ville);
    }

    // DELETE /villes/{id} - Supprime une ville
    public function destroy($id)
    {
        $ville = Ville::findOrFail($id);
        $ville->delete();
        return response()->json(null, 204);
    }
}