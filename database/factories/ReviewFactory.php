<?php

namespace Database\Factories;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reservation_id' => Reservation::factory()->terminee(),
            'note' => fake()->numberBetween(3, 5),
            'commentaire' => fake()->randomElement([
                'Trajet agreable, conducteur ponctuel.',
                'Tres bonne experience, je recommande.',
                'Correct, rien a signaler.',
                'Conducteur sympathique et prudent.',
                null,
            ]),
        ];
    }
}
