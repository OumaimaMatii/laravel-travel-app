<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class HotelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'adresse' => $this->adresse,
            'etoiles' => $this->etoiles,
            'image_principale' => $this->image_principale ? Storage::url($this->image_principale) : null,
            'ville_id' => $this->ville_id,
            'ville' => new VilleResource($this->whenLoaded('ville')),
            'type_chambres' => TypeChambreResource::collection($this->whenLoaded('typeChambres')),
            'medias' => MediaResource::collection($this->whenLoaded('medias')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}