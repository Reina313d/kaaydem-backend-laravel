<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Enums\TripStatus;
use App\Exceptions\CreneauChevauchantException;
use App\Exceptions\PlacesInsuffisantesException;
use App\Models\Reservation;
use App\Models\Trip;
use App\Models\User;
use App\Notifications\KaaydemNotification;
use Illuminate\Support\Facades\DB;

class ReservationService
{
    /**
     * Creer une reservation pour un passager sur un trajet.
     *
     * @throws PlacesInsuffisantesException
     * @throws CreneauChevauchantException
     */
    public function reserver(Trip $trip, User $passager, int $nombrePlaces): Reservation
    {
        return DB::transaction(function () use ($trip, $passager, $nombrePlaces) {
            // Verrouille la ligne du trajet pour eviter les conditions de course
            $trip = Trip::whereKey($trip->id)->lockForUpdate()->firstOrFail();

            if ($trip->statut !== TripStatus::Publie) {
                throw new PlacesInsuffisantesException("Ce trajet n'accepte plus de reservations.");
            }

            if ($trip->places_disponibles < $nombrePlaces) {
                throw new PlacesInsuffisantesException(
                    "Places insuffisantes : {$trip->places_disponibles} place(s) disponible(s) seulement."
                );
            }

            $this->verifierChevauchement($trip, $passager);

            $reservation = new Reservation([
                'trip_id' => $trip->id,
                'passenger_id' => $passager->id,
                'nombre_places' => $nombrePlaces,
                'statut' => ReservationStatus::EnAttente,
                'historique_transitions' => [[
                    'de' => null,
                    'vers' => ReservationStatus::EnAttente->value,
                    'acteur_id' => $passager->id,
                    'date' => now()->toIso8601String(),
                ]],
            ]);
            $reservation->save();

            // Decrementation immediate des places pour eviter la survente pendant l'attente
            $trip->decrement('places_disponibles', $nombrePlaces);

            $trip->driver->notify(new KaaydemNotification(
                'Nouvelle demande de réservation',
                "{$passager->nom} souhaite réserver {$nombrePlaces} place(s) sur votre trajet {$trip->ville_depart} → {$trip->ville_arrivee}.",
                $trip->id,
            ));

            return $reservation->fresh(['trip', 'passenger']);
        });
    }

    /**
     * Empeche de reserver deux trajets qui se chevauchent dans le temps pour le meme passager.
     *
     * @throws CreneauChevauchantException
     */
    private function verifierChevauchement(Trip $trip, User $passager): void
    {
        $fenetreHeures = 3; // hypothese: un trajet "occupe" le passager +/- 3h autour du depart

        $debut = $trip->date_heure_depart->copy()->subHours($fenetreHeures);
        $fin = $trip->date_heure_depart->copy()->addHours($fenetreHeures);

        $chevauchement = Reservation::where('passenger_id', $passager->id)
            ->whereIn('statut', [ReservationStatus::EnAttente, ReservationStatus::Confirmee])
            ->whereHas('trip', function ($q) use ($debut, $fin) {
                $q->whereBetween('date_heure_depart', [$debut, $fin]);
            })
            ->exists();

        if ($chevauchement) {
            throw new CreneauChevauchantException();
        }
    }

    /**
     * Le conducteur accepte une reservation.
     */
    public function accepter(Reservation $reservation, User $conducteur): Reservation
    {
        return DB::transaction(function () use ($reservation, $conducteur) {
            $reservation->ajouterTransition(ReservationStatus::Confirmee, $conducteur->id);
            $reservation->save();

            $reservation->passenger->notify(new KaaydemNotification(
                'Réservation confirmée',
                "Votre réservation pour le trajet {$reservation->trip->ville_depart} → {$reservation->trip->ville_arrivee} a été confirmée par le conducteur.",
                $reservation->trip_id,
            ));

            return $reservation->fresh(['trip', 'passenger']);
        });
    }

    /**
     * Le conducteur refuse une reservation : les places sont recreditees.
     */
    public function refuser(Reservation $reservation, User $conducteur): Reservation
    {
        return DB::transaction(function () use ($reservation, $conducteur) {
            $reservation->ajouterTransition(ReservationStatus::Refusee, $conducteur->id);
            $reservation->save();

            $reservation->trip->increment('places_disponibles', $reservation->nombre_places);

            $reservation->passenger->notify(new KaaydemNotification(
                'Réservation refusée',
                "Votre réservation pour le trajet {$reservation->trip->ville_depart} → {$reservation->trip->ville_arrivee} a été refusée par le conducteur.",
                $reservation->trip_id,
            ));

            return $reservation->fresh(['trip', 'passenger']);
        });
    }

    /**
     * Le passager (ou le conducteur) annule une reservation : les places sont recreditees.
     */
    public function annuler(Reservation $reservation, User $acteur): Reservation
    {
        return DB::transaction(function () use ($reservation, $acteur) {
            $statutPrecedent = $reservation->statut;

            $reservation->ajouterTransition(ReservationStatus::Annulee, $acteur->id);
            $reservation->save();

            if (in_array($statutPrecedent, [ReservationStatus::EnAttente, ReservationStatus::Confirmee])) {
                $reservation->trip->increment('places_disponibles', $reservation->nombre_places);
            }

            // On notifie l'autre partie : si c'est le passager qui annule, le conducteur
            // est informe, et inversement.
            $estAnnuleeParLePassager = $acteur->id === $reservation->passenger_id;
            $destinataire = $estAnnuleeParLePassager ? $reservation->trip->driver : $reservation->passenger;

            $destinataire->notify(new KaaydemNotification(
                'Réservation annulée',
                "La réservation sur le trajet {$reservation->trip->ville_depart} → {$reservation->trip->ville_arrivee} a été annulée.",
                $reservation->trip_id,
            ));

            return $reservation->fresh(['trip', 'passenger']);
        });
    }

    /**
     * Marque une reservation confirmee comme terminee (a la cloture du trajet).
     */
    public function terminer(Reservation $reservation, User $acteur): Reservation
    {
        $reservation->ajouterTransition(ReservationStatus::Terminee, $acteur->id);
        $reservation->save();

        return $reservation;
    }
}
