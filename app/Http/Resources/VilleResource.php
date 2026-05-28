<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VilleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'destinations' => DestinationResource::collection($this->whenLoaded('destinations')),
            'hotels' => HotelResource::collection($this->whenLoaded('hotels')),
        ];
    }
}