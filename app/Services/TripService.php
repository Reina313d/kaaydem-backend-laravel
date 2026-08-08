<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Enums\TripStatus;
use App\Exceptions\TrajetNonModifiableException;
use App\Models\Trip;
use App\Models\User;
use App\Notifications\KaaydemNotification;
use Illuminate\Support\Facades\DB;

class TripService
{
    public function creer(User $conducteur, array $donnees): Trip
    {
        return Trip::create([
            'driver_id' => $conducteur->id,
            'ville_depart' => $donnees['ville_depart'],
            'ville_arrivee' => $donnees['ville_arrivee'],
            'points_arret' => $donnees['points_arret'] ?? [],
            'date_heure_depart' => $donnees['date_heure_depart'],
            'places_totales' => $donnees['places_totales'],
            'places_disponibles' => $donnees['places_totales'],
            'prix_place' => $donnees['prix_place'],
            'statut' => TripStatus::Publie,
        ]);
    }

    /**
     * @throws TrajetNonModifiableException
     */
    public function mettreAJour(Trip $trip, array $donnees): Trip
    {
        if (! $trip->estModifiable()) {
            throw new TrajetNonModifiableException();
        }

        // Si on reduit les places totales, on ajuste les places disponibles en consequence
        if (isset($donnees['places_totales'])) {
            $delta = $donnees['places_totales'] - $trip->places_totales;
            $donnees['places_disponibles'] = max(0, $trip->places_disponibles + $delta);
        }

        $trip->update($donnees);

        return $trip->fresh();
    }

    /**
     * @throws TrajetNonModifiableException
     */
    public function annuler(Trip $trip, User $acteur): Trip
    {
        if (! $trip->estModifiable()) {
            throw new TrajetNonModifiableException();
        }

        return DB::transaction(function () use ($trip, $acteur) {
            $trip->update(['statut' => TripStatus::Annule]);

            // Les reservations encore en attente n'ont plus lieu d'etre : on les annule
            // et on informe chaque passager concerne.
            $trip->reservations()
                ->where('statut', ReservationStatus::EnAttente)
                ->get()
                ->each(function ($reservation) use ($acteur, $trip) {
                    $reservation->ajouterTransition(ReservationStatus::Annulee, $acteur->id);
                    $reservation->save();

                    $reservation->passenger->notify(new KaaydemNotification(
                        'Trajet annulé',
                        "Le trajet {$trip->ville_depart} → {$trip->ville_arrivee} a été annulé par le conducteur.",
                        $trip->id,
                    ));
                });

            return $trip->fresh();
        });
    }

    /**
     * Cloture un trajet : passe le trajet a "termine" et toutes les reservations
     * confirmees a "terminee".
     */
    public function cloturer(Trip $trip, User $conducteur): Trip
    {
        return DB::transaction(function () use ($trip, $conducteur) {
            $trip->update(['statut' => TripStatus::Termine]);

            $trip->reservations()
                ->where('statut', ReservationStatus::Confirmee)
                ->get()
                ->each(function ($reservation) use ($conducteur) {
                    $reservation->ajouterTransition(ReservationStatus::Terminee, $conducteur->id);
                    $reservation->save();
                });

            return $trip->fresh(['reservations']);
        });
    }
}
