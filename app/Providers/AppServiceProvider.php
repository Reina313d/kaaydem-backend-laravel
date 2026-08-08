<?php

namespace App\Providers;

use App\Models\Reservation;
use App\Models\Trip;
use App\Policies\ReservationPolicy;
use App\Policies\TripPolicy;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Necessaire pour MySQL/MariaDB anterieurs (ex: version fournie avec Laragon) :
        // evite l'erreur 1071 "Specified key was too long" sur les colonnes
        // VARCHAR(255) uniques encodees en utf8mb4.
        Builder::defaultStringLength(191);

        Gate::policy(Trip::class, TripPolicy::class);
        Gate::policy(Reservation::class, ReservationPolicy::class);
    }
}