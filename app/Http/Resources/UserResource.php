<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'campus' => $this->campus,
            'photo' => $this->photo,
            // Alias attendu par le frontend (avatar Navbar/Profil) ; meme valeur que "photo",
            // qui contient deja une URL complete (cf. ProfileController::update).
            'photo_url' => $this->photo,
            'is_admin' => (bool) $this->is_admin,
            'actif' => (bool) $this->actif,
            'est_conducteur_valide' => $this->estConducteurValide(),
            'driver_profile' => new DriverProfileResource($this->whenLoaded('driverProfile')),
            'note_moyenne_conducteur' => $this->when(
                $this->estConducteurValide(),
                fn () => $this->noteMoyenneConducteur()
            ),
            'nombre_avis_conducteur' => $this->when(
                $this->estConducteurValide(),
                fn () => $this->nombreAvisConducteur()
            ),
            'created_at' => $this->created_at,
        ];
    }
}
