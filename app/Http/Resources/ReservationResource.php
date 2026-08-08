<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre_places' => $this->nombre_places,
            'statut' => $this->statut->value,
            'statut_label' => $this->statut->label(),
            'historique_transitions' => $this->historique_transitions ?? [],
            'trip' => new TripResource($this->whenLoaded('trip')),
            'passager' => new UserResource($this->whenLoaded('passenger')),
            'review' => new ReviewResource($this->whenLoaded('review')),
            'created_at' => $this->created_at,
        ];
    }
}
