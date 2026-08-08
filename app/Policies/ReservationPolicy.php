<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    /**
     * Seul le passager auteur ou le conducteur du trajet peuvent consulter la reservation.
     */
    public function view(User $user, Reservation $reservation): bool
    {
        return $user->id === $reservation->passenger_id
            || $user->id === $reservation->trip->driver_id;
    }

    /**
     * Seul le passager peut annuler sa propre reservation.
     */
    public function cancel(User $user, Reservation $reservation): bool
    {
        return $user->id === $reservation->passenger_id;
    }

    /**
     * Seul le conducteur du trajet peut accepter/refuser une reservation.
     */
    public function decide(User $user, Reservation $reservation): bool
    {
        return $user->id === $reservation->trip->driver_id;
    }

    /**
     * Seul le passager qui a voyage peut evaluer le conducteur.
     */
    public function review(User $user, Reservation $reservation): bool
    {
        return $user->id === $reservation->passenger_id;
    }
}
