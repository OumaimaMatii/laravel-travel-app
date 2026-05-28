<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HotelTypeChambreResource;
use App\Models\HotelTypeChambre;
use Illuminate\Http\Request;

class HotelTypeChambreController extends Controller
{
    // GET /hotel-type-chambres - Liste toutes les associations hôtel/type de chambre
    public function index()
    {
        $items = HotelTypeChambre::with('hotel', 'typeChambre')->get();
        return HotelTypeChambreResource::collection($items);
    }

    // POST /hotel-type-chambres - Crée une association hôtel/type de chambre
    public function store(Request $request)
    {
        $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'type_chambre_id' => 'required|exists:type_chambres,id',
            'quantite_disponible' => 'required|integer|min:0',
            'prix_par_nuit' => 'required|numeric|min:0',
        ]);
        $item = HotelTypeChambre::create($request->all());
        return new HotelTypeChambreResource($item);
    }

    // GET /hotel-type-chambres/{id} - Affiche une association spécifique
    public function show($id)
    {
        $item = HotelTypeChambre::with('hotel', 'typeChambre')->findOrFail($id);
        return new HotelTypeChambreResource($item);
    }

    // PUT/PATCH /hotel-type-chambres/{id} - Met à jour une association
    public function update(Request $request, $id)
    {
        $item = HotelTypeChambre::findOrFail($id);
        $item->update($request->all());
        return new HotelTypeChambreResource($item);
    }

    // DELETE /hotel-type-chambres/{id} - Supprime une association
    public function destroy($id)
    {
        $item = HotelTypeChambre::findOrFail($id);
        $item->delete();
        return response()->json(null, 204);
    }
}