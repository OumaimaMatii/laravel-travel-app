<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelTypeChambreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantite_disponible' => $this->quantite_disponible,
            'prix_par_nuit' => $this->prix_par_nuit,
            'hotel' => new HotelResource($this->whenLoaded('hotel')),
            'type_chambre' => new TypeChambreResource($this->whenLoaded('typeChambre')),
        ];
    }
}