<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DetailReservationResource;
use App\Models\DetailReservation;
use Illuminate\Http\Request;

class DetailReservationController extends Controller
{
    // GET /detail-reservations - Liste tous les détails de réservation (filtrable par reservation_id)
    public function index(Request $request)
    {
        $query = DetailReservation::with('reservation', 'hotel', 'typeChambre');

        if ($request->has('reservation_id')) {
            $query->where('reservation_id', $request->reservation_id);
        }

        return DetailReservationResource::collection($query->get());
    }

    // GET /client/reservations/{id}/details-chambres - Récupère les détails chambres d'une réservation
    public function byReservation($reservationId)
    {
        $details = DetailReservation::with(['hotel', 'typeChambre'])
            ->where('reservation_id', $reservationId)
            ->get();

        return DetailReservationResource::collection($details);
    }

    // POST /detail-reservations - Crée un détail de réservation (chambre)
    public function store(Request $request)
    {
        $request->validate([
            'reservation_id'  => 'required|exists:reservations,id',
            'hotel_id'        => 'required|exists:hotels,id',
            'type_chambre_id' => 'required|exists:type_chambres,id',
            'quantite'        => 'required|integer|min:1',
            'prix_unitaire'   => 'required|numeric|min:0',
        ]);

        $detail = DetailReservation::create($request->all());
        return new DetailReservationResource($detail->load('hotel', 'typeChambre'));
    }

    // GET /detail-reservations/{id} - Affiche un détail de réservation
    public function show($id)
    {
        $detail = DetailReservation::with('reservation', 'hotel', 'typeChambre')->findOrFail($id);
        return new DetailReservationResource($detail);
    }

    // PUT/PATCH /detail-reservations/{id} - Met à jour un détail de réservation
    public function update(Request $request, $id)
    {
        $detail = DetailReservation::findOrFail($id);
        $detail->update($request->all());
        return new DetailReservationResource($detail->load('hotel', 'typeChambre'));
    }

    // DELETE /detail-reservations/{id} - Supprime un détail de réservation
    public function destroy($id)
    {
        DetailReservation::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}