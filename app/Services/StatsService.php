<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Review;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StatsService
{
    public function tableauDeBord(): array
    {
        return [
            'trajets_par_mois' => $this->trajetsParMois(),
            'taux_occupation' => $this->tauxOccupation(),
            'top_conducteurs' => $this->topConducteurs(),
            'totaux' => [
                'utilisateurs' => User::count(),
                'conducteurs_valides' => User::whereHas('driverProfile', fn ($q) => $q->where('statut_validation', 'valide'))->count(),
                'trajets' => Trip::count(),
                'reservations' => Reservation::count(),
                'signalements_ouverts' => DB::table('reports')->where('statut', 'ouvert')->count(),
            ],
        ];
    }

    private function trajetsParMois(): array
    {
        return Trip::selectRaw("DATE_FORMAT(date_heure_depart, '%Y-%m') as mois, COUNT(*) as total")
            ->groupBy('mois')
            ->orderBy('mois')
            ->limit(12)
            ->get()
            ->toArray();
    }

    private function tauxOccupation(): float
    {
        $trip = Trip::selectRaw('SUM(places_totales) as total_places, SUM(places_totales - places_disponibles) as places_occupees')
            ->first();

        if (! $trip || ! $trip->total_places) {
            return 0.0;
        }

        return round(($trip->places_occupees / $trip->total_places) * 100, 1);
    }

    private function topConducteurs(int $limite = 5): array
    {
        return User::query()
            ->whereHas('driverProfile', fn ($q) => $q->where('statut_validation', 'valide'))
            ->withCount('trajetsConduits')
            ->get()
            ->map(fn ($conducteur) => [
                'id' => $conducteur->id,
                'nom' => $conducteur->nom,
                'nombre_trajets' => $conducteur->trajets_conduits_count,
                'note_moyenne' => $conducteur->noteMoyenneConducteur(),
            ])
            ->sortByDesc('nombre_trajets')
            ->take($limite)
            ->values()
            ->toArray();
    }
}
