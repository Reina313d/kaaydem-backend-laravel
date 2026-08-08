<?php

namespace Tests\Feature;

use App\Models\DriverProfile;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private function conducteurValide(): User
    {
        $conducteur = User::factory()->create();
        DriverProfile::factory()->for($conducteur)->create();

        return $conducteur;
    }

    public function test_le_conducteur_est_notifie_d_une_nouvelle_demande_de_reservation(): void
    {
        $conducteur = $this->conducteurValide();
        $passager = User::factory()->create();

        $trajet = Trip::factory()->for($conducteur, 'driver')->create([
            'places_totales' => 2,
            'places_disponibles' => 2,
        ]);

        $this->actingAs($passager)
            ->postJson("/api/v1/trips/{$trajet->id}/reservations", ['nombre_places' => 1])
            ->assertOk();

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $conducteur->id,
        ]);
    }

    public function test_le_passager_est_notifie_quand_sa_reservation_est_confirmee(): void
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
            ->patchJson("/api/v1/reservations/{$reservationId}/accept")
            ->assertOk();

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $passager->id,
        ]);
    }

    public function test_un_utilisateur_peut_lister_et_marquer_ses_notifications_comme_lues(): void
    {
        $conducteur = $this->conducteurValide();
        $passager = User::factory()->create();

        $trajet = Trip::factory()->for($conducteur, 'driver')->create([
            'places_totales' => 2,
            'places_disponibles' => 2,
        ]);

        $this->actingAs($passager)
            ->postJson("/api/v1/trips/{$trajet->id}/reservations", ['nombre_places' => 1])
            ->assertOk();

        $notificationId = $this->actingAs($conducteur)
            ->getJson('/api/v1/notifications')
            ->assertOk()
            ->json('data.0.id');

        $this->actingAs($conducteur)
            ->patchJson("/api/v1/notifications/{$notificationId}/read")
            ->assertOk()
            ->assertJsonPath('data.lu', true);
    }
}
