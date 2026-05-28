<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoyageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date_depart' => $this->date_depart,
            'date_retour' => $this->date_retour,
            'destination' => new DestinationResource($this->whenLoaded('destination')),
            'type_voyage' => $this->typeVoyage?->nom,
            'forfait' => $this->whenLoaded('forfait', function() {
                return [
                    'prix_adulte' => $this->forfait->prix_adulte,
                    'prix_enfant' => $this->forfait->prix_enfant,
                    'nombre_places' => $this->forfait->nombre_places,
                    'places_restantes' => $this->forfait->places_restantes,
                    'programme' => $this->forfait->programme,
                    'hotel' => new HotelResource($this->forfait->hotel),
                    'statut' => $this->forfait->statut?->nom,
                    'agent' => $this->forfait->agent?->name,
                ];
            }),
            'sur_mesure' => $this->whenLoaded('surMesure', function() {
                return [
                    'budget_estime' => $this->surMesure->budget_estime,
                    'client' => $this->surMesure->client?->name,
                    'statut' => $this->surMesure->statut?->nom,
                ];
            }),
            'activites' => ActiviteResource::collection($this->whenLoaded('activites')),
            'transports' => TransportResource::collection($this->whenLoaded('transports')),
            'reservations_count' => $this->reservations->count(),
        ];
    }
}