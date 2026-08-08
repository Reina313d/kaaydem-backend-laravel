<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'telephone' => ['nullable', 'string', 'max:20'],
            'campus' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => "Un compte existe deja avec cet email.",
            'password.min' => "Le mot de passe doit contenir au moins 8 caracteres.",
            'password.confirmed' => "La confirmation du mot de passe ne correspond pas.",
        ];
    }
}
