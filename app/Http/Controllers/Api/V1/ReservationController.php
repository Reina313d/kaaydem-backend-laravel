<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use App\Models\Trip;
use App\Services\ReservationService;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function __construct(private readonly ReservationService $reservationService)
    {
    }

    public function store(StoreReservationRequest $request, Trip $trip)
    {
        $reservation = $this->reservationService->reserver(
            $trip,
            $request->user(),
            $request->validated('nombre_places')
        );

        return new ReservationResource($reservation->load(['trip', 'passenger']));
    }

    public function accept(Request $request, Reservation $reservation)
    {
        $this->authorize('decide', $reservation);

        $reservation = $this->reservationService->accepter($reservation, $request->user());

        return new ReservationResource($reservation);
    }

    public function refuse(Request $request, Reservation $reservation)
    {
        $this->authorize('decide', $reservation);

        $reservation = $this->reservationService->refuser($reservation, $request->user());

        return new ReservationResource($reservation);
    }

    public function cancel(Request $request, Reservation $reservation)
    {
        $estPassager = $request->user()->id === $reservation->passenger_id;
        $estConducteur = $request->user()->id === $reservation->trip->driver_id;

        if (! $estPassager && ! $estConducteur) {
            abort(403, "Vous ne pouvez pas annuler cette reservation.");
        }

        $reservation = $this->reservationService->annuler($reservation, $request->user());

        return new ReservationResource($reservation);
    }

    /**
     * Mes reservations (passager).
     */
    public function mesReservations(Request $request)
    {
        $reservations = $request->user()
            ->reservations()
            ->with(['trip.driver', 'review'])
            ->latest()
            ->paginate($request->query('par_page', 10));

        return ReservationResource::collection($reservations);
    }
}
