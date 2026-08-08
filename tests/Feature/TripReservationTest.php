<?php

namespace Tests\Feature;

use App\Models\DriverProfile;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripReservationTest extends TestCase
{
    use RefreshDatabase;

    private function conducteurValide(): User
    {
        $conducteur = User::factory()->create();
        DriverProfile::factory()->for($conducteur)->create();

        return $conducteur;
    }

    public function test_un_conducteur_valide_peut_publier_un_trajet(): void
    {
        $conducteur = $this->conducteurValide();

        $reponse = $this->actingAs($conducteur)->postJson('/api/v1/trips', [
            'ville_depart' => 'Dakar',
            'ville_arrivee' => 'Diamniadio',
            'date_heure_depart' => now()->addDay()->toIso8601String(),
            'places_totales' => 3,
            'prix_place' => 1000,
        ]);

        $reponse->assertStatus(201);
        $this->assertDatabaseHas('trips', ['ville_depart' => 'Dakar', 'places_disponibles' => 3]);
    }

    public function test_un_utilisateur_non_conducteur_ne_peut_pas_publier_de_trajet(): void
    {
        $utilisateur = User::factory()->create();

        $reponse = $this->actingAs($utilisateur)->postJson('/api/v1/trips', [
            'ville_depart' => 'Dakar',
            'ville_arrivee' => 'Diamniadio',
            'date_heure_depart' => now()->addDay()->toIso8601String(),
            'places_totales' => 3,
            'prix_place' => 1000,
        ]);

        $reponse->assertStatus(403);
    }

    public function test_reservation_refusee_si_places_insuffisantes(): void
    {
        $conducteur = $this->conducteurValide();
        $passager = User::factory()->create();

        $trajet = Trip::factory()->for($conducteur, 'driver')->create([
            'places_totales' => 1,
            'places_disponibles' => 1,
        ]);

        $reponse = $this->actingAs($passager)->postJson("/api/v1/trips/{$trajet->id}/reservations", [
            'nombre_places' => 2,
        ]);

        $reponse->assertStatus(409);
    }

    public function test_le_conducteur_peut_accepter_une_reservation(): void
    {
        $conducteur = $this->conducteurValide();
        $passager = User::factory()->create();

        $trajet = Trip::factory()->for($conducteur, 'driver')->create([
            'places_totales' => 2,
            'places_disponibles' => 2,
        ]);

        $reservationId = $this->actingAs($passager)
            ->postJson("/api/v1/trips/{$trajet->id}/reservations", ['nombre_places' => 1])
            ->json('data.id');

        $reponse = $this->actingAs($conducteur)
            ->patchJson("/api/v1/reservations/{$reservationId}/accept");

        $reponse->assertOk()->assertJsonPath('data.statut', 'confirmee');
    }

    public function test_seul_le_proprietaire_peut_modifier_son_trajet(): void
    {
        $conducteur = $this->conducteurValide();
        $autreConducteur = $this->conducteurValide();

        $trajet = Trip::factory()->for($conducteur, 'driver')->create();

        $this->actingAs($autreConducteur)
            ->putJson("/api/v1/trips/{$trajet->id}", ['prix_place' => 5000])
            ->assertStatus(403);
    }

    public function test_annuler_un_trajet_annule_aussi_les_reservations_en_attente(): void
    {
        $conducteur = $this->conducteurValide();
        $passager = User::factory()->create();

        $trajet = Trip::factory()->for($conducteur, 'driver')->create([
            'places_totales' => 2,
            'places_disponibles' => 2,
        ]);

        $reservationId = $this->actingAs($passager)
            ->postJson("/api/v1/trips/{$trajet->id}/reservations", ['nombre_places' => 1])
            ->json('data.id');

        $this->actingAs($conducteur)
            ->deleteJson("/api/v1/trips/{$trajet->id}")
            ->assertOk()
            ->assertJsonPath('data.statut', 'annule');

        $this->assertDatabaseHas('reservations', [
            'id' => $reservationId,
            'statut' => 'annulee',
        ]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $passager->id,
        ]);
    }
}
