<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TypeChambreResource;
use App\Models\TypeChambre;
use Illuminate\Http\Request;

class TypeChambreController extends Controller
{
    // GET /type-chambres - Liste tous les types de chambres
    public function index()
    {
        $types = TypeChambre::with('hotels')->get();
        return TypeChambreResource::collection($types);
    }

    // POST /type-chambres - Crée un type de chambre
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'capacite_max' => 'required|integer|min:1',
        ]);
        $type = TypeChambre::create($request->all());
        return new TypeChambreResource($type);
    }

    // GET /type-chambres/{id} - Affiche un type de chambre
    public function show($id)
    {
        $type = TypeChambre::with('hotels')->findOrFail($id);
        return new TypeChambreResource($type);
    }

    // PUT/PATCH /type-chambres/{id} - Met à jour un type de chambre
    public function update(Request $request, $id)
    {
        $type = TypeChambre::findOrFail($id);
        $type->update($request->all());
        return new TypeChambreResource($type);
    }

    // DELETE /type-chambres/{id} - Supprime un type de chambre
    public function destroy($id)
    {
        $type = TypeChambre::findOrFail($id);
        $type->delete();
        return response()->json(null, 204);
    }
}