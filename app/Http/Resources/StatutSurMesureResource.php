<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatutSurMesureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'voyages' => VoyageSurMesureResource::collection($this->whenLoaded('voyagesSurMesure')),
        ];
    }
}