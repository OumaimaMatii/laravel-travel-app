<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TypeVoyageResource;
use App\Models\TypeVoyage;
use Illuminate\Http\Request;

class TypeVoyageController extends Controller
{
    // GET /type-voyages - Liste tous les types de voyage
    public function index()
    {
        $types = TypeVoyage::with('voyages')->get();
        return TypeVoyageResource::collection($types);
    }

    // POST /type-voyages - Crée un type de voyage (forfait ou sur_mesure)
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255|in:forfait,sur_mesure',
        ]);
        $type = TypeVoyage::create($request->all());
        return new TypeVoyageResource($type);
    }

    // GET /type-voyages/{id} - Affiche un type de voyage
    public function show($id)
    {
        $type = TypeVoyage::with('voyages')->findOrFail($id);
        return new TypeVoyageResource($type);
    }

    // PUT/PATCH /type-voyages/{id} - Met à jour un type de voyage
    public function update(Request $request, $id)
    {
        $type = TypeVoyage::findOrFail($id);
        $type->update($request->all());
        return new TypeVoyageResource($type);
    }

    // DELETE /type-voyages/{id} - Supprime un type de voyage
    public function destroy($id)
    {
        $type = TypeVoyage::findOrFail($id);
        $type->delete();
        return response()->json(null, 204);
    }
}