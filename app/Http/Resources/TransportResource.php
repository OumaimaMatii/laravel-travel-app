<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'compagnie' => $this->compagnie,
            'numero_vol' => $this->numero_vol,
            
            'ville_depart' => $this->villeDepart ? [
                'id' => $this->villeDepart->id,
                'nom' => $this->villeDepart->nom,
            ] : null,
            
            'ville_arrivee' => $this->villeArrivee ? [
                'id' => $this->villeArrivee->id,
                'nom' => $this->villeArrivee->nom,
            ] : null,
            
            'depart' => $this->depart,
            'arrivee' => $this->arrivee,
            
            'heure_depart' => $this->heure_depart,
            'heure_arrivee' => $this->heure_arrivee,
            'prix' => (float) $this->prix,
            'places_disponibles' => $this->places_disponibles,
            'type' => new TypeTransportResource($this->whenLoaded('typeTransport')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}