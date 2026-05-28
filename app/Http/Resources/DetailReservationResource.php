<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailReservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantite' => $this->quantite,
            'prix_unitaire' => $this->prix_unitaire,
            'reservation' => new ReservationResource($this->whenLoaded('reservation')),
            'hotel' => new HotelResource($this->whenLoaded('hotel')),
            'type_chambre' => new TypeChambreResource($this->whenLoaded('typeChambre')),
        ];
    }
}