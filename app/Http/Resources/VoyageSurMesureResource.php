<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoyageSurMesureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'budget_estime' => $this->budget_estime,
            
            'voyage' => [
                'id' => $this->voyage?->id,
                'date_depart' => $this->voyage?->date_depart,
                'date_retour' => $this->voyage?->date_retour,
                'titre' => $this->voyage?->titre,
                'description' => $this->voyage?->description,
                'duree' => $this->voyage ? (new \DateTime($this->voyage->date_retour))->diff(new \DateTime($this->voyage->date_depart))->days : null,
            ],
            
            'destination' => new DestinationResource($this->whenLoaded('voyage.destination')),
            
            'client' => $this->client ? [
                'id' => $this->client->id,
                'name' => $this->client->name,
                'email' => $this->client->email,
            ] : null,
            
            'activites' => ActiviteResource::collection($this->whenLoaded('voyage.activites')),
            'transports' => TransportResource::collection($this->whenLoaded('voyage.transports')),
            
            'statut' => $this->statut ? [
                'id' => $this->statut->id,
                'nom' => $this->statut->nom,
                'couleur' => $this->statut->couleur,
            ] : null,
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}