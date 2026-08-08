<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_utilisateur_peut_s_inscrire(): void
    {
        $reponse = $this->postJson('/api/v1/register', [
            'nom' => 'Test Etudiant',
            'email' => 'test@isep.sn',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $reponse->assertStatus(201)
            ->assertJsonStructure(['user', 'token']);

        $this->assertDatabaseHas('users', ['email' => 'test@isep.sn']);
    }

    public function test_un_utilisateur_peut_se_connecter(): void
    {
        $user = User::factory()->create([
            'email' => 'connexion@isep.sn',
            'password' => bcrypt('motdepasse'),
        ]);

        $reponse = $this->postJson('/api/v1/login', [
            'email' => 'connexion@isep.sn',
            'password' => 'motdepasse',
        ]);

        $reponse->assertOk()->assertJsonStructure(['user', 'token']);
    }

    public function test_connexion_refusee_avec_mauvais_mot_de_passe(): void
    {
        User::factory()->create([
            'email' => 'echec@isep.sn',
            'password' => bcrypt('bonmotdepasse'),
        ]);

        $reponse = $this->postJson('/api/v1/login', [
            'email' => 'echec@isep.sn',
            'password' => 'mauvais',
        ]);

        $reponse->assertStatus(422);
    }

    public function test_un_visiteur_ne_peut_pas_acceder_a_son_profil(): void
    {
        $this->getJson('/api/v1/me')->assertStatus(401);
    }
}
