<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ReservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $voyage = $this->voyage;

        return [
            'id'                     => $this->id,
            'date_reservation'       => $this->date_reservation,
            'statut'                 => $this->statut,
            'type_verification'      => $this->type_verification,
            'nb_adultes'             => $this->nb_adultes,
            'nb_enfants'             => $this->nb_enfants,
            'montant_total'          => $this->montant_total,
            'mode_paiement'          => $this->mode_paiement,
            'date_paiement'          => $this->date_paiement,
            'confirmation_deadline'  => $this->confirmation_deadline,
            'notification_envoyee'   => (bool) $this->notification_envoyee,

            'client' => new UserResource($this->whenLoaded('user')),

            'voyage' => $voyage ? [
                'id'          => $voyage->id,
                'titre'       => $voyage->titre,
                'date_depart' => $voyage->date_depart,
                'date_retour' => $voyage->date_retour,
                'destination' => $voyage->relationLoaded('destination') ? [
                    'id'              => $voyage->destination->id,
                    'nom'             => $voyage->destination->nom,
                    'pays'            => $voyage->destination->pays,
                    'image_couverture'=> $voyage->destination->image_couverture
                        ? Storage::url($voyage->destination->image_couverture)
                        : null,
                    'ville'           => $voyage->destination->ville ? [
                        'id'  => $voyage->destination->ville->id,
                        'nom' => $voyage->destination->ville->nom,
                    ] : null,
                    'medias'          => $voyage->destination->relationLoaded('medias')
                        ? MediaResource::collection($voyage->destination->medias)->toArray($request)
                        : [],
                ] : null,
                'transports'  => $voyage->relationLoaded('transports')
                    ? TransportResource::collection($voyage->transports)
                    : [],
                'activites'   => $voyage->relationLoaded('activites')
                    ? ActiviteResource::collection($voyage->activites)
                    : [],
            ] : null,

            'voyageurs' => VoyageurResource::collection($this->whenLoaded('voyageurs')),

            'details' => DetailReservationResource::collection($this->whenLoaded('detailReservations')),

            'details_chambres' => DetailReservationResource::collection($this->whenLoaded('detailReservations')),

            'documents' => $this->whenLoaded('documents'),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}