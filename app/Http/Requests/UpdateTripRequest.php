<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ville_depart' => ['sometimes', 'string', 'max:255'],
            'ville_arrivee' => ['sometimes', 'string', 'max:255', 'different:ville_depart'],
            'points_arret' => ['nullable', 'array'],
            'points_arret.*' => ['string', 'max:255'],
            'date_heure_depart' => ['sometimes', 'date', 'after:now'],
            'places_totales' => ['sometimes', 'integer', 'min:1', 'max:8'],
            'prix_place' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
