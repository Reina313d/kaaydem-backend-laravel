<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_utilisateur_peut_signaler_un_autre_utilisateur(): void
    {
        $auteur = User::factory()->create();
        $signale = User::factory()->create();

        $reponse = $this->actingAs($auteur)->postJson('/api/v1/reports', [
            'utilisateur_signale_id' => $signale->id,
            'motif' => "Comportement inapproprie pendant le trajet.",
        ]);

        $reponse->assertStatus(201)
            ->assertJsonPath('data.utilisateur_signale.id', $signale->id);

        $this->assertDatabaseHas('reports', [
            'auteur_id' => $auteur->id,
            'utilisateur_signale_id' => $signale->id,
        ]);
    }

    public function test_un_utilisateur_ne_peut_pas_se_signaler_lui_meme(): void
    {
        $utilisateur = User::factory()->create();

        $reponse = $this->actingAs($utilisateur)->postJson('/api/v1/reports', [
            'utilisateur_signale_id' => $utilisateur->id,
            'motif' => "Test d'auto-signalement.",
        ]);

        $reponse->assertStatus(422);
    }
}
