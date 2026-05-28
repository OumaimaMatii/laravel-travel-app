<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransportResource;
use App\Models\Transport;
use App\Models\TypeTransport;
use Illuminate\Http\Request;

class TransportController extends Controller
{
    // GET /transports - Liste tous les transports
    public function index()
    {
        $transports = Transport::with('typeTransport', 'voyages')->get();
        return TransportResource::collection($transports);
    }

    // GET /transports/forfait - Transports internes aux forfaits (bus privés)
    public function getForfaitTransports()
    {
        $transports = Transport::with('typeTransport')
            ->where(function ($q) {
                $q->where('numero_vol', 'like', 'FORFAIT-%')
                  ->orWhere('numero_vol', 'like', 'ALLER-%')
                  ->orWhere('numero_vol', 'like', 'RETOUR-%');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $transports]);
    }

    // GET /transports/publics - Transports publics pour le sur-mesure (filtrable par ville)
    public function getPublicTransports(Request $request)
    {
        $query = Transport::with('typeTransport')
            ->where(function ($q) {
                $q->whereNull('numero_vol')
                  ->orWhere(function ($q2) {
                      $q2->where('numero_vol', 'not like', 'FORFAIT-%')
                         ->where('numero_vol', 'not like', 'ALLER-%')
                         ->where('numero_vol', 'not like', 'RETOUR-%');
                  });
            });

        // Filtre par ville de départ
        if ($request->filled('ville_depart')) {
            $query->where('depart', 'like', '%' . $request->ville_depart . '%');
        }

        $transports = $query->orderBy('compagnie')->get();

        return response()->json([
            'success' => true,
            'data'    => TransportResource::collection($transports),
        ]);
    }

    // POST /transports - Crée un transport
    public function store(Request $request)
    {
        $request->validate([
            'compagnie'         => 'required|string|max:255',
            'type_transport_id' => 'required|exists:type_transports,id',
        ]);

        $transport = Transport::create($request->all());
        return new TransportResource($transport);
    }

    // GET /transports/{id} - Affiche un transport spécifique
    public function show($id)
    {
        return new TransportResource(Transport::with('typeTransport', 'voyages')->findOrFail($id));
    }

    // PUT/PATCH /transports/{id} - Met à jour un transport
    public function update(Request $request, $id)
    {
        $transport = Transport::findOrFail($id);
        $transport->update($request->all());
        return new TransportResource($transport->load('typeTransport'));
    }

    // DELETE /transports/{id} - Supprime un transport
    public function destroy($id)
    {
        Transport::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}