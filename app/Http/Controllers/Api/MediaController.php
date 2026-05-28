<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\MediaRequest;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use App\Traits\ImageUploadTrait;

class MediaController extends Controller
{
    use ImageUploadTrait;

    // GET /medias - Liste tous les médias (images)
    public function index()
    {
        $medias = Media::with('mediable')->get();
        return MediaResource::collection($medias);
    }

    // POST /medias - Crée un nouveau média (upload d'image)
    public function store(MediaRequest $request)
    {
        $data = $request->validated();
        
        if ($request->hasFile('image')) {
            $data['url'] = $this->uploadImage($request->file('image'), 'medias');
        }
        
        // Attribution automatique de l'ordre si non fourni
        if (!isset($data['ordre'])) {
            $maxOrdre = Media::where('mediable_type', $data['mediable_type'])
                            ->where('mediable_id', $data['mediable_id'])
                            ->max('ordre');
            $data['ordre'] = ($maxOrdre ?? -1) + 1;
        }
        
        $media = Media::create($data);
        return new MediaResource($media->load('mediable'));
    }

    // GET /medias/{id} - Affiche un média spécifique
    public function show($id)
    {
        $media = Media::with('mediable')->findOrFail($id);
        return new MediaResource($media);
    }

    // PUT/PATCH /medias/{id} - Met à jour un média
    public function update(MediaRequest $request, $id)
    {
        $media = Media::findOrFail($id);
        $data = $request->validated();
        
        if ($request->hasFile('image')) {
            $data['url'] = $this->uploadImage($request->file('image'), 'medias', $media->url);
        }
        
        $media->update($data);
        return new MediaResource($media->load('mediable'));
    }

    // DELETE /medias/{id} - Supprime un média et son fichier
    public function destroy($id)
    {
        $media = Media::findOrFail($id);
        
        if ($media->url) {
            $this->deleteImage($media->url);
        }
        
        $media->delete();
        return response()->json(null, 204);
    }

    // POST /medias/multiple - Upload multiple d'images
    public function storeMultiple(Request $request)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'mediable_type' => 'required|string',
            'mediable_id' => 'required|integer',
        ]);

        $medias = [];
        $ordre = Media::where('mediable_type', $request->mediable_type)
                     ->where('mediable_id', $request->mediable_id)
                     ->max('ordre') ?? -1;

        foreach ($request->file('images') as $file) {
            $ordre++;
            $path = $this->uploadImage($file, 'medias');
            
            $media = Media::create([
                'url' => $path,
                'titre' => $request->titre ?? null,
                'ordre' => $ordre,
                'est_principale' => $ordre === 0,
                'mediable_type' => $request->mediable_type,
                'mediable_id' => $request->mediable_id,
            ]);
            
            $medias[] = $media;
        }

        return MediaResource::collection($medias);
    }
}