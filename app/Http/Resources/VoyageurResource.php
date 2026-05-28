<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoyageurResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom_complet' => $this->nom_complet,
            'date_naissance' => $this->date_naissance,
            'sexe' => $this->sexe,
            'numero_passeport' => $this->numero_passeport,
            'reservation' => new ReservationResource($this->whenLoaded('reservation')),
        ];
    }
}