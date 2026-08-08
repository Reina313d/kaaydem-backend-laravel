<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TripFactory extends Factory
{
    public function definition(): array
    {
        $villes = ['Dakar', 'Rufisque', 'Diamniadio', 'Sebikotane', 'Bargny', 'Pikine'];
        [$depart, $arrivee] = fake()->randomElements($villes, 2);
        $placesTotales = fake()->numberBetween(1, 4);

        return [
            'driver_id' => User::factory(),
            'ville_depart' => $depart,
            'ville_arrivee' => $arrivee,
            'points_arret' => fake()->boolean(40) ? [fake()->randomElement($villes)] : [],
            'date_heure_depart' => fake()->dateTimeBetween('+1 day', '+3 weeks'),
            'places_totales' => $placesTotales,
            'places_disponibles' => $placesTotales,
            'prix_place' => fake()->randomElement([500, 750, 1000, 1500, 2000]),
            'statut' => 'publie',
        ];
    }

    public function termine(): static
    {
        return $this->state(fn () => [
            'statut' => 'termine',
            'date_heure_depart' => fake()->dateTimeBetween('-1 month', '-1 day'),
        ]);
    }
}
