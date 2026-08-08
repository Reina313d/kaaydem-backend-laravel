<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DriverRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'numero_permis' => ['required', 'string', 'max:50'],
            'vehicule_marque' => ['required', 'string', 'max:100'],
            'vehicule_modele' => ['required', 'string', 'max:100'],
            'vehicule_immatriculation' => ['required', 'string', 'max:20'],
        ];
    }
}
