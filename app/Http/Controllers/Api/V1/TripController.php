<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TripStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTripRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Http\Resources\TripResource;
use App\Models\Trip;
use App\Services\TripService;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function __construct(private readonly TripService $tripService)
    {
    }

    /**
     * Recherche publique paginee: ville depart / arrivee / date, filtres prix max et places,
     * tri et pagination cote API.
     */
    public function index(Request $request)
    {
        $query = Trip::query()
            ->with('driver')
            ->where('statut', TripStatus::Publie)
            ->where('date_heure_depart', '>=', now())
            ->recherche(
                $request->query('depart'),
                $request->query('arrivee'),
                $request->query('date'),
            );

        if ($request->filled('prix_max')) {
            $query->where('prix_place', '<=', (int) $request->query('prix_max'));
        }

        if ($request->filled('places_min')) {
            $query->where('places_disponibles', '>=', (int) $request->query('places_min'));
        }

        $tri = $request->query('tri', 'date_heure_depart');
        $direction = $request->query('direction', 'asc');
        $colonnesAutorisees = ['date_heure_depart', 'prix_place', 'places_disponibles'];

        if (in_array($tri, $colonnesAutorisees, true)) {
            $query->orderBy($tri, $direction === 'desc' ? 'desc' : 'asc');
        }

        $trajets = $query->paginate($request->query('par_page', 10));

        return TripResource::collection($trajets);
    }

    public function store(StoreTripRequest $request)
    {
        $this->authorizeConducteur($request);

        $trajet = $this->tripService->creer($request->user(), $request->validated());

        return new TripResource($trajet->load('driver'));
    }

    public function show(Trip $trip)
    {
        return new TripResource($trip->load(['driver', 'reservations.passenger']));
    }

    public function update(UpdateTripRequest $request, Trip $trip)
    {
        $this->authorize('update', $trip);

        $trajet = $this->tripService->mettreAJour($trip, $request->validated());

        return new TripResource($trajet->load('driver'));
    }

    public function destroy(Request $request, Trip $trip)
    {
        $this->authorize('delete', $trip);

        $trajet = $this->tripService->annuler($trip, $request->user());

        return new TripResource($trajet);
    }

    public function close(Request $request, Trip $trip)
    {
        $this->authorize('close', $trip);

        $trajet = $this->tripService->cloturer($trip, $request->user());

        return new TripResource($trajet->load('driver'));
    }

    /**
     * Mes trajets (conducteur) avec reservations recues.
     */
    public function mesTrajets(Request $request)
    {
        $trajets = $request->user()
            ->trajetsConduits()
            ->with('reservations.passenger')
            ->latest('date_heure_depart')
            ->paginate($request->query('par_page', 10));

        return TripResource::collection($trajets);
    }

    private function authorizeConducteur(Request $request): void
    {
        if (! $request->user()->estConducteurValide()) {
            abort(403, "Seul un conducteur valide par l'administration peut publier un trajet.");
        }
    }
}
