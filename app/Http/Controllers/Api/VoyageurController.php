<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voyageur;
use Illuminate\Http\Request;

class VoyageurController extends Controller
{
    // GET /voyageurs - Liste tous les voyageurs
    public function index()
    {
        return response()->json(Voyageur::with('reservation')->get());
    }

    // POST /voyageurs - Crée un voyageur
    public function store(Request $request)
    {
        $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'nom_complet' => 'required|string',
            'date_naissance' => 'required|date',
            'sexe' => 'required|in:homme,femme',
        ]);
        $voyageur = Voyageur::create($request->all());
        return response()->json($voyageur, 201);
    }

    // GET /voyageurs/{id} - Affiche un voyageur spécifique
    public function show($id)
    {
        return response()->json(Voyageur::with('reservation')->findOrFail($id));
    }

    // PUT/PATCH /voyageurs/{id} - Met à jour un voyageur
    public function update(Request $request, $id)
    {
        $voyageur = Voyageur::findOrFail($id);
        $voyageur->update($request->all());
        return response()->json($voyageur);
    }

    // DELETE /voyageurs/{id} - Supprime un voyageur
    public function destroy($id)
    {
        $voyageur = Voyageur::findOrFail($id);
        $voyageur->delete();
        return response()->json(null, 204);
    }
}