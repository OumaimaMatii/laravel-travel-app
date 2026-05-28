<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActiviteRequest;
use App\Http\Resources\ActiviteResource;
use App\Models\Activite;
use App\Traits\ImageUploadTrait;

class ActiviteController extends Controller
{
    use ImageUploadTrait;

    // GET /activites - Liste toutes les activités avec leurs destinations et médias
    public function index()
    {
        $activites = Activite::with('destination', 'medias')->get();
        return ActiviteResource::collection($activites);
    }

    // POST /activites - Crée une nouvelle activité (upload d'image possible)
    public function store(ActiviteRequest $request)
    {
        $data = $request->validated();
        
        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadImage($request->file('image'), 'activites');
        }
        
        $activite = Activite::create($data);
        return new ActiviteResource($activite->load('destination', 'medias'));
    }

    // GET /activites/{id} - Affiche une activité spécifique avec ses relations
    public function show($id)
    {
        $activite = Activite::with('destination', 'medias', 'voyages')->findOrFail($id);
        return new ActiviteResource($activite);
    }

    // PUT/PATCH /activites/{id} - Met à jour une activité (changement d'image possible)
    public function update(ActiviteRequest $request, $id)
    {
        $activite = Activite::findOrFail($id);
        $data = $request->validated();
        
        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadImage($request->file('image'), 'activites', $activite->image);
        }
        
        $activite->update($data);
        return new ActiviteResource($activite->load('destination', 'medias'));
    }

    // DELETE /activites/{id} - Supprime une activité et son image associée
    public function destroy($id)
    {
        $activite = Activite::findOrFail($id);
        
        if ($activite->image) {
            $this->deleteImage($activite->image);
        }
        
        $activite->delete();
        return response()->json(null, 204);
    }
}