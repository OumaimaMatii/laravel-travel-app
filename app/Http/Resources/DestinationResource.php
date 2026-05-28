<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DestinationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'pays' => $this->pays,
            'image_couverture' => $this->image_couverture ? Storage::url($this->image_couverture) : null,
            'actif' => (bool) $this->actif,
            'ville_id' => $this->ville_id,
            'ville' => new VilleResource($this->whenLoaded('ville')),
            'activites' => ActiviteResource::collection($this->whenLoaded('activites')),
            'voyages' => VoyageResource::collection($this->whenLoaded('voyages')),
            'medias' => MediaResource::collection($this->whenLoaded('medias')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}