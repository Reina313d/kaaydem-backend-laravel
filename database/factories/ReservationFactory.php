<?php

namespace Database\Factories;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'trip_id' => Trip::factory(),
            'passenger_id' => User::factory(),
            'nombre_places' => fake()->numberBetween(1, 2),
            'statut' => 'en_attente',
            'historique_transitions' => [[
                'de' => null,
                'vers' => 'en_attente',
                'acteur_id' => null,
                'date' => now()->toIso8601String(),
            ]],
        ];
    }

    public function confirmee(): static
    {
        return $this->state(fn () => ['statut' => 'confirmee']);
    }

    public function terminee(): static
    {
        return $this->state(fn () => ['statut' => 'terminee']);
    }
}
