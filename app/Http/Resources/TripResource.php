<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ville_depart' => $this->ville_depart,
            'ville_arrivee' => $this->ville_arrivee,
            'points_arret' => $this->points_arret ?? [],
            'date_heure_depart' => $this->date_heure_depart,
            'places_totales' => $this->places_totales,
            'places_disponibles' => $this->places_disponibles,
            'prix_place' => $this->prix_place,
            'statut' => $this->statut->value,
            'statut_label' => $this->statut->label(),
            'est_modifiable' => $this->estModifiable(),
            'conducteur' => new UserResource($this->whenLoaded('driver')),
            'reservations' => ReservationResource::collection($this->whenLoaded('reservations')),
            'created_at' => $this->created_at,
        ];
    }
}
