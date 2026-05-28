<?php

namespace App\Http\Resources;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoyageForfaitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $voyage = $this->voyage;

        return [
            'id'               => $this->id,
            'titre'            => $this->titre,
            'description'      => $this->description,
            'prix_adulte'      => (float) $this->prix_adulte,
            'prix_enfant'      => $this->prix_enfant ? (float) $this->prix_enfant : null,
            'nombre_places'    => $this->nombre_places,
            'places_restantes' => $this->places_restantes,
            'programme'        => $this->programme,
            'type_verification'=> $this->type_verification ?? 'forfait',

            'type_forfait' => $this->typeForfait ? [
                'id'  => $this->typeForfait->id,
                'nom' => $this->typeForfait->nom,
            ] : null,

            'voyage' => $voyage ? [
                'id'          => $voyage->id,
                'date_depart' => $voyage->date_depart,
                'date_retour' => $voyage->date_retour,
                'duree'       => $voyage->date_depart && $voyage->date_retour
                    ? (new \DateTime($voyage->date_retour))->diff(new \DateTime($voyage->date_depart))->days
                    : null,
                'ville_depart'      => $voyage->villeDepart ? [
                    'id'  => $voyage->villeDepart->id,
                    'nom' => $voyage->villeDepart->nom,
                ] : null,
                'ville_depart_id'   => $voyage->ville_depart_id,
            ] : null,

            'destination' => $voyage && $voyage->destination ? [
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
                    ? MediaResource::collection($voyage->destination->medias)
                    : [],
            ] : null,

            'hotel' => $this->hotel ? [
                'id'              => $this->hotel->id,
                'nom'             => $this->hotel->nom,
                'adresse'         => $this->hotel->adresse,
                'etoiles'         => $this->hotel->etoiles,
                'image_principale'=> $this->hotel->image_principale
                    ? Storage::url($this->hotel->image_principale)
                    : null,
                'ville'           => $this->hotel->ville ? [
                    'id'  => $this->hotel->ville->id,
                    'nom' => $this->hotel->ville->nom,
                ] : null,
                'medias'          => $this->hotel->relationLoaded('medias')
                    ? MediaResource::collection($this->hotel->medias)
                    : [],
            ] : null,

            'activites' => $voyage && $voyage->relationLoaded('activites')
                ? ActiviteResource::collection($voyage->activites)
                : [],

            'transports' => $voyage && $voyage->relationLoaded('transports')
                ? $voyage->transports->map(fn ($t) => [
                    'id'               => $t->id,
                    'compagnie'        => $t->compagnie,
                    'numero_vol'       => $t->numero_vol,
                    'depart'           => $t->depart,
                    'arrivee'          => $t->arrivee,
                    'heure_depart'     => $t->heure_depart,
                    'heure_arrivee'    => $t->heure_arrivee,
                    'prix'             => (float) $t->prix,
                    'ordre'            => $t->pivot->ordre ?? null,
                    'type'             => $t->typeTransport ? [
                        'id'  => $t->typeTransport->id,
                        'nom' => $t->typeTransport->nom,
                    ] : null,
                ])->sortBy('ordre')->values()
                : [],

            'transport' => $voyage && $voyage->relationLoaded('transports')
                ? ($voyage->transports->firstWhere('pivot.ordre', 1) ? [
                    'id'           => $voyage->transports->firstWhere('pivot.ordre', 1)->id,
                    'compagnie'    => $voyage->transports->firstWhere('pivot.ordre', 1)->compagnie,
                    'prix'         => (float) $voyage->transports->firstWhere('pivot.ordre', 1)->prix,
                    'ordre'        => 1,
                    'type'         => $voyage->transports->firstWhere('pivot.ordre', 1)->typeTransport ? [
                        'id'  => $voyage->transports->firstWhere('pivot.ordre', 1)->typeTransport->id,
                        'nom' => $voyage->transports->firstWhere('pivot.ordre', 1)->typeTransport->nom,
                    ] : null,
                ] : null)
                : null,

            'transport_retour' => $voyage && $voyage->relationLoaded('transports')
                ? ($voyage->transports->firstWhere('pivot.ordre', 2) ? [
                    'id'           => $voyage->transports->firstWhere('pivot.ordre', 2)->id,
                    'compagnie'    => $voyage->transports->firstWhere('pivot.ordre', 2)->compagnie,
                    'prix'         => (float) $voyage->transports->firstWhere('pivot.ordre', 2)->prix,
                    'ordre'        => 2,
                    'type'         => $voyage->transports->firstWhere('pivot.ordre', 2)->typeTransport ? [
                        'id'  => $voyage->transports->firstWhere('pivot.ordre', 2)->typeTransport->id,
                        'nom' => $voyage->transports->firstWhere('pivot.ordre', 2)->typeTransport->nom,
                    ] : null,
                ] : null)
                : null,

            'statut' => $this->statut ? [
                'id'  => $this->statut->id,
                'nom' => $this->statut->nom,
            ] : null,

            'agent' => $this->agent ? [
                'id'   => $this->agent->id,
                'name' => $this->agent->name,
            ] : null,

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}