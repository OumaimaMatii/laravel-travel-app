<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VoyageTransportResource;
use App\Models\VoyageTransport;
use Illuminate\Http\Request;

class VoyageTransportController extends Controller
{
    // GET /voyage-transports - Liste les associations voyage/transport
    public function index()
    {
        $items = VoyageTransport::with('voyage', 'transport')->get();
        return VoyageTransportResource::collection($items);
    }

    // POST /voyage-transports - Crée une association voyage/transport
    public function store(Request $request)
    {
        $request->validate([
            'voyage_id' => 'required|exists:voyages,id',
            'transport_id' => 'required|exists:transports,id',
            'ordre' => 'required|integer|min:1|max:3',
        ]);
        $item = VoyageTransport::create($request->all());
        return new VoyageTransportResource($item);
    }

    // GET /voyage-transports/{id} - Affiche une association spécifique
    public function show($id)
    {
        $item = VoyageTransport::with('voyage', 'transport')->findOrFail($id);
        return new VoyageTransportResource($item);
    }

    // PUT/PATCH /voyage-transports/{id} - Met à jour une association
    public function update(Request $request, $id)
    {
        $item = VoyageTransport::findOrFail($id);
        $item->update($request->all());
        return new VoyageTransportResource($item);
    }

    // DELETE /voyage-transports/{id} - Supprime une association
    public function destroy($id)
    {
        $item = VoyageTransport::findOrFail($id);
        $item->delete();
        return response()->json(null, 204);
    }
}