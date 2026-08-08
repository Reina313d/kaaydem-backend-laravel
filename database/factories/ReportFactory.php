<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'auteur_id' => User::factory(),
            'utilisateur_signale_id' => User::factory(),
            'trip_id' => null,
            'motif' => fake()->randomElement([
                "Le conducteur n'est jamais venu au point de rendez-vous.",
                "Comportement irrespectueux pendant le trajet.",
                "Le vehicule ne correspondait pas a celui annonce.",
                "Retard important sans prevenir les passagers.",
            ]),
            'statut' => 'ouvert',
        ];
    }

    public function traite(string $statut = 'resolu'): static
    {
        return $this->state(fn () => ['statut' => $statut]);
    }
}
