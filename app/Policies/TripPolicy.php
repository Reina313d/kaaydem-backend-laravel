<?php

namespace App\Policies;

use App\Models\Trip;
use App\Models\User;

class TripPolicy
{
    public function view(User $user, Trip $trip): bool
    {
        return true;
    }

    public function update(User $user, Trip $trip): bool
    {
        return $user->id === $trip->driver_id;
    }

    public function delete(User $user, Trip $trip): bool
    {
        return $user->id === $trip->driver_id;
    }

    public function close(User $user, Trip $trip): bool
    {
        return $user->id === $trip->driver_id;
    }

    public function manageReservations(User $user, Trip $trip): bool
    {
        return $user->id === $trip->driver_id;
    }
}
