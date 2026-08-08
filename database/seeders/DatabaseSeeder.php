<?php

namespace Database\Seeders;

use App\Models\DriverProfile;
use App\Models\Report;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Genere une base de demonstration complete pour la soutenance.
     * Comptes de test (mot de passe pour tous : "password") :
     *   - admin@kaaydem.sn        (administrateur)
     *   - conducteur1@kaaydem.sn  (conducteur valide)
     *   - conducteur2@kaaydem.sn  (conducteur en attente de validation)
     *   - passager1@kaaydem.sn    (passager)
     *   - passager2@kaaydem.sn    (passager)
     */
    public function run(): void
    {
        // --- Comptes de test nommes ---
        $admin = User::factory()->admin()->create([
            'nom' => 'Admin Kaay Dem',
            'email' => 'admin@kaaydem.sn',
        ]);

        $conducteur1 = User::factory()->create([
            'nom' => 'Moussa Diop',
            'email' => 'conducteur1@kaaydem.sn',
            'campus' => 'Diamniadio',
        ]);
        DriverProfile::factory()->for($conducteur1)->create();

        $conducteur2 = User::factory()->create([
            'nom' => 'Fatou Ndiaye',
            'email' => 'conducteur2@kaaydem.sn',
            'campus' => 'Dakar',
        ]);
        DriverProfile::factory()->enAttente()->for($conducteur2)->create();

        $passager1 = User::factory()->create([
            'nom' => 'Awa Sarr',
            'email' => 'passager1@kaaydem.sn',
            'campus' => 'Rufisque',
        ]);

        $passager2 = User::factory()->create([
            'nom' => 'Ibrahima Fall',
            'email' => 'passager2@kaaydem.sn',
            'campus' => 'Diamniadio',
        ]);

        // --- Utilisateurs et conducteurs supplementaires (volume pour les stats) ---
        $autresConducteurs = User::factory()->count(6)->create();
        foreach ($autresConducteurs as $conducteur) {
            DriverProfile::factory()->for($conducteur)->create();
        }

        $autresPassagers = User::factory()->count(15)->create();

        $tousConducteurs = collect([$conducteur1])->merge($autresConducteurs);
        $tousPassagers = collect([$passager1, $passager2])->merge($autresPassagers);

        // --- Trajets a venir (publies, ouverts a la reservation) ---
        $trajetsAVenir = Trip::factory()
            ->count(20)
            ->recycle($tousConducteurs)
            ->create();

        // Reservations en attente / confirmees sur des trajets a venir
        foreach ($trajetsAVenir->random(12) as $trajet) {
            Reservation::factory()
                ->recycle($tousPassagers)
                ->for($trajet)
                ->state(['statut' => fake()->randomElement(['en_attente', 'confirmee'])])
                ->create();
        }

        // --- Trajets termines (historique, pour les evaluations et stats) ---
        $trajetsTermines = Trip::factory()
            ->count(15)
            ->termine()
            ->recycle($tousConducteurs)
            ->create();

        foreach ($trajetsTermines as $trajet) {
            $reservation = Reservation::factory()
                ->recycle($tousPassagers)
                ->for($trajet)
                ->terminee()
                ->create();

            if (fake()->boolean(70)) {
                Review::factory()->for($reservation)->create();
            }
        }

        // --- Quelques signalements de demonstration ---
        for ($i = 0; $i < 4; $i++) {
            Report::factory()->create([
                'auteur_id' => $tousPassagers->random()->id,
                'utilisateur_signale_id' => $tousConducteurs->random()->id,
            ]);
        }

        Report::factory()->traite('resolu')->create([
            'auteur_id' => $passager2->id,
            'utilisateur_signale_id' => $conducteur1->id,
        ]);

        $this->command->info('Base de demonstration "Kaay Dem !" generee avec succes.');
        $this->command->table(
            ['Role', 'Email', 'Mot de passe'],
            [
                ['Administrateur', 'admin@kaaydem.sn', 'password'],
                ['Conducteur (valide)', 'conducteur1@kaaydem.sn', 'password'],
                ['Conducteur (en attente)', 'conducteur2@kaaydem.sn', 'password'],
                ['Passager', 'passager1@kaaydem.sn', 'password'],
                ['Passager', 'passager2@kaaydem.sn', 'password'],
            ]
        );
    }
}
