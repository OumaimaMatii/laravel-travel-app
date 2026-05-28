<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TypeTransportResource;
use App\Models\TypeTransport;
use Illuminate\Http\Request;

class TypeTransportController extends Controller
{
    // GET /type-transports - Liste tous les types de transport
    public function index()
    {
        $types = TypeTransport::with('transports')->get();
        return TypeTransportResource::collection($types);
    }

    // GET /type-transports/forfait - Récupère les types de transport pour les forfaits (uniquement les bus)
    public function getForfaitTypes()
    {
        $types = TypeTransport::where('nom', 'like', '%Bus%')
                    ->orWhere('nom', 'like', '%Mini-bus%')
                    ->orderBy('id', 'asc')
                    ->get();
        
        return response()->json([
            'success' => true,
            'data' => $types
        ]);
    }

    // POST /type-transports - Crée un type de transport
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
        ]);
        $type = TypeTransport::create($request->all());
        return new TypeTransportResource($type);
    }

    // GET /type-transports/{id} - Affiche un type de transport
    public function show($id)
    {
        $type = TypeTransport::with('transports')->findOrFail($id);
        return new TypeTransportResource($type);
    }

    // PUT/PATCH /type-transports/{id} - Met à jour un type de transport
    public function update(Request $request, $id)
    {
        $type = TypeTransport::findOrFail($id);
        $type->update($request->all());
        return new TypeTransportResource($type);
    }

    // DELETE /type-transports/{id} - Supprime un type de transport
    public function destroy($id)
    {
        $type = TypeTransport::findOrFail($id);
        $type->delete();
        return response()->json(null, 204);
    }
}