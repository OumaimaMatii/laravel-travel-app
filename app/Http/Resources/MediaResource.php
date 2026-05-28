<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url ? Storage::url($this->url) : null,
            'titre' => $this->titre,
            'ordre' => $this->ordre,
            'est_principale' => (bool) $this->est_principale,
            'mediable_type' => $this->mediable_type,
            'mediable_id' => $this->mediable_id,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}