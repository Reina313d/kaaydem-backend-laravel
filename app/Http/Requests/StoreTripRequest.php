<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ville_depart' => ['required', 'string', 'max:255'],
            'ville_arrivee' => ['required', 'string', 'max:255', 'different:ville_depart'],
            'points_arret' => ['nullable', 'array'],
            'points_arret.*' => ['string', 'max:255'],
            'date_heure_depart' => ['required', 'date', 'after:now'],
            'places_totales' => ['required', 'integer', 'min:1', 'max:8'],
            'prix_place' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'ville_arrivee.different' => "La ville d'arrivee doit etre differente de la ville de depart.",
            'date_heure_depart.after' => "La date de depart doit etre dans le futur.",
        ];
    }
}
