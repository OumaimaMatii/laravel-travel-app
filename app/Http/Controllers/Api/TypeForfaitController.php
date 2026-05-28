<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TypeForfait;
use Illuminate\Http\Request;

class TypeForfaitController extends Controller
{
    // GET /type-forfaits - Liste tous les types de forfaits (public)
    public function index()
    {
        $types = TypeForfait::all();
        return response()->json([
            'success' => true,
            'data' => $types
        ]);
    }

    // POST /type-forfaits - Crée un nouveau type (admin uniquement)
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255|unique:type_forfaits'
        ]);

        $type = TypeForfait::create([
            'nom' => $request->nom
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Type créé avec succès',
            'data' => $type
        ], 201);
    }

    // DELETE /type-forfaits/{id} - Supprime un type (admin uniquement)
    public function destroy($id)
    {
        $type = TypeForfait::findOrFail($id);
        
        // Vérification si le type est utilisé
        if ($type->voyagesForfait()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer : des forfaits utilisent ce type'
            ], 400);
        }
        
        $type->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Type supprimé avec succès'
        ], 200);
    }

    // PUT/PATCH /type-forfaits/{id} - Modifie un type (admin uniquement)
    public function update(Request $request, $id)
    {
        $type = TypeForfait::findOrFail($id);
        
        $request->validate([
            'nom' => 'required|string|max:255|unique:type_forfaits,nom,' . $id
        ]);

        $type->update([
            'nom' => $request->nom
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Type modifié avec succès',
            'data' => $type
        ]);
    }

    // GET /type-forfaits/{id} - Affiche un type spécifique
    public function show($id)
    {
        $type = TypeForfait::with('voyagesForfait')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $type
        ]);
    }
}