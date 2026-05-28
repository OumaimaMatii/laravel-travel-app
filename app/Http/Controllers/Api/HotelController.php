<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\HotelRequest;
use App\Http\Resources\HotelResource;
use App\Models\Hotel;
use App\Traits\ImageUploadTrait;

class HotelController extends Controller
{
    use ImageUploadTrait;

    // GET /hotels - Liste tous les hôtels avec leurs villes, types de chambres et médias
    public function index()
    {
        $hotels = Hotel::with('ville', 'typeChambres', 'medias')->get();
        return HotelResource::collection($hotels);
    }

    // POST /hotels - Crée un nouvel hôtel (upload d'image possible)
    public function store(HotelRequest $request)
    {
        $data = $request->validated();
        
        if ($request->hasFile('image_principale')) {
            $data['image_principale'] = $this->uploadImage($request->file('image_principale'), 'hotels');
        }
        
        $hotel = Hotel::create($data);
        return new HotelResource($hotel->load('ville', 'typeChambres', 'medias'));
    }

    // GET /hotels/{id} - Affiche un hôtel spécifique
    public function show($id)
    {
        $hotel = Hotel::with('ville', 'typeChambres', 'medias')->findOrFail($id);
        return new HotelResource($hotel);
    }

    // PUT/PATCH /hotels/{id} - Met à jour un hôtel
    public function update(HotelRequest $request, $id)
    {
        $hotel = Hotel::findOrFail($id);
        $data = $request->validated();
        
        if ($request->hasFile('image_principale')) {
            $data['image_principale'] = $this->uploadImage($request->file('image_principale'), 'hotels', $hotel->image_principale);
        }
        
        $hotel->update($data);
        return new HotelResource($hotel->load('ville', 'typeChambres', 'medias'));
    }

    // DELETE /hotels/{id} - Supprime un hôtel et son image
    public function destroy($id)
    {
        $hotel = Hotel::findOrFail($id);
        
        if ($hotel->image_principale) {
            $this->deleteImage($hotel->image_principale);
        }
        
        $hotel->delete();
        return response()->json(null, 204);
    }
}