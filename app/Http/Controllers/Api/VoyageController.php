<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VoyageResource;
use App\Models\Voyage;
use Illuminate\Http\Request;

class VoyageController extends Controller
{
    // GET /voyages - Liste tous les voyages (pour l'administration)
    public function index()
    {
        $voyages = Voyage::with([
            'destination',
            'typeVoyage',
            'villeDepart'
        ])->get();

        return VoyageResource::collection($voyages);
    }

    // GET /voyages/{id} - Affiche un voyage spécifique
    public function show($id)
    {
        $voyage = Voyage::with([
            'destination',
            'forfait',
            'surMesure',
            'villeDepart'
        ])->findOrFail($id);

        return new VoyageResource($voyage);
    }
}