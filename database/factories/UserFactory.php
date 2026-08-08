<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        $campus = fake()->randomElement(['Diamniadio', 'Dakar', 'Rufisque']);

        return [
            'nom' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'telephone' => '7'.fake()->numerify('# ### ## ##'),
            'campus' => $campus,
            'photo' => null,
            'is_admin' => false,
            'actif' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['is_admin' => true]);
    }

    public function inactif(): static
    {
        return $this->state(fn () => ['actif' => false]);
    }
}
