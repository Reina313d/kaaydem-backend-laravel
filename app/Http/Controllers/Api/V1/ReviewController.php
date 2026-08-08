<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Reservation;
use App\Models\Review;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Reservation $reservation)
    {
        $this->authorize('review', $reservation);

        if ($reservation->statut !== ReservationStatus::Terminee) {
            throw ValidationException::withMessages([
                'note' => ["Vous ne pouvez evaluer le conducteur qu'apres la cloture du trajet."],
            ]);
        }

        if ($reservation->review()->exists()) {
            throw ValidationException::withMessages([
                'note' => ["Ce trajet a deja ete evalue."],
            ]);
        }

        $review = Review::create([
            'reservation_id' => $reservation->id,
            ...$request->validated(),
        ]);

        return new ReviewResource($review);
    }
}
