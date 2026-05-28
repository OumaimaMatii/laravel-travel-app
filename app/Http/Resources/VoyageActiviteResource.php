<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoyageActiviteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'voyage' => new VoyageResource($this->whenLoaded('voyage')),
            'activite' => new ActiviteResource($this->whenLoaded('activite')),
        ];
    }
}