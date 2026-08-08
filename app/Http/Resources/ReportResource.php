<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'motif' => $this->motif,
            'statut' => $this->statut->value,
            'trip_id' => $this->trip_id,
            'auteur' => new UserResource($this->whenLoaded('auteur')),
            'utilisateur_signale' => new UserResource($this->whenLoaded('utilisateurSignale')),
            'created_at' => $this->created_at,
        ];
    }
}
