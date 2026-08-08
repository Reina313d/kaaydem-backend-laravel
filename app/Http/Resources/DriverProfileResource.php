<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (! $this->resource) {
            return [];
        }

        return [
            'id' => $this->id,
            'numero_permis' => $this->numero_permis,
            'vehicule' => [
                'marque' => $this->vehicule_marque,
                'modele' => $this->vehicule_modele,
                'immatriculation' => $this->vehicule_immatriculation,
            ],
            'statut_validation' => $this->statut_validation->value,
            'motif_rejet' => $this->motif_rejet,
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
