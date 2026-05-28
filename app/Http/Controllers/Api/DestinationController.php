<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestinationRequest;
use App\Http\Resources\DestinationResource;
use App\Models\Destination;
use App\Traits\ImageUploadTrait;

class DestinationController extends Controller
{
    use ImageUploadTrait;

    // GET /destinations - Liste toutes les destinations avec leurs villes, activités et médias
    public function index()
    {
        $destinations = Destination::with('ville', 'activites', 'medias')->get();
        return DestinationResource::collection($destinations);
    }

    // POST /destinations - Crée une nouvelle destination (upload d'image possible)
    public function store(DestinationRequest $request)
    {
        $data = $request->validated();
        
        if ($request->hasFile('image_couverture')) {
            $data['image_couverture'] = $this->uploadImage($request->file('image_couverture'), 'destinations');
        }
        
        $destination = Destination::create($data);
        return new DestinationResource($destination->load('ville', 'activites', 'medias'));
    }

    // GET /destinations/{id} - Affiche une destination spécifique
    public function show($id)
    {
        $destination = Destination::with('ville', 'activites', 'voyages', 'medias')->findOrFail($id);
        return new DestinationResource($destination);
    }

    // PUT/PATCH /destinations/{id} - Met à jour une destination
    public function update(DestinationRequest $request, $id)
    {
        $destination = Destination::findOrFail($id);
        $data = $request->validated();
        
        if ($request->hasFile('image_couverture')) {
            $data['image_couverture'] = $this->uploadImage($request->file('image_couverture'), 'destinations', $destination->image_couverture);
        }
        
        $destination->update($data);
        return new DestinationResource($destination->load('ville', 'activites', 'medias'));
    }

    // DELETE /destinations/{id} - Supprime une destination et son image
    public function destroy($id)
    {
        $destination = Destination::findOrFail($id);
        
        if ($destination->image_couverture) {
            $this->deleteImage($destination->image_couverture);
        }
        
        $destination->delete();
        return response()->json(null, 204);
    }
}