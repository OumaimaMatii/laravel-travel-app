<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoyageTransportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ordre' => $this->ordre,
            'voyage' => new VoyageResource($this->whenLoaded('voyage')),
            'transport' => new TransportResource($this->whenLoaded('transport')),
        ];
    }
}