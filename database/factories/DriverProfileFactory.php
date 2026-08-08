<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DriverProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'numero_permis' => strtoupper(fake()->bothify('SN-#####??')),
            'vehicule_marque' => fake()->randomElement(['Toyota', 'Hyundai', 'Renault', 'Peugeot', 'Kia']),
            'vehicule_modele' => fake()->randomElement(['Corolla', 'Accent', 'Clio', '208', 'Picanto']),
            'vehicule_immatriculation' => strtoupper(fake()->bothify('DK-####-??')),
            'statut_validation' => 'valide',
        ];
    }

    public function enAttente(): static
    {
        return $this->state(fn () => ['statut_validation' => 'en_attente']);
    }
}
