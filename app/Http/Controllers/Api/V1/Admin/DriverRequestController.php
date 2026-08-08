<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\DriverValidationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\DriverProfileResource;
use App\Models\DriverProfile;
use App\Notifications\KaaydemNotification;
use Illuminate\Http\Request;

class DriverRequestController extends Controller
{
    public function index(Request $request)
    {
        $profils = DriverProfile::with('user')
            ->when(
                $request->filled('statut'),
                fn ($q) => $q->where('statut_validation', $request->query('statut'))
            )
            ->latest()
            ->paginate($request->query('par_page', 15));

        return DriverProfileResource::collection($profils);
    }

    public function update(Request $request, DriverProfile $driverRequest)
    {
        $request->validate([
            'statut_validation' => ['required', 'in:valide,rejete'],
            'motif_rejet' => ['required_if:statut_validation,rejete', 'nullable', 'string', 'max:500'],
        ]);

        $driverRequest->update([
            'statut_validation' => DriverValidationStatus::from($request->input('statut_validation')),
            'motif_rejet' => $request->input('statut_validation') === 'rejete'
                ? $request->input('motif_rejet')
                : null,
        ]);

        $driverRequest->loadMissing('user');

        if ($driverRequest->statut_validation === DriverValidationStatus::Valide) {
            $driverRequest->user->notify(new KaaydemNotification(
                'Statut conducteur validé',
                "Votre demande de statut conducteur a été validée. Vous pouvez desormais publier des trajets.",
            ));
        } else {
            $driverRequest->user->notify(new KaaydemNotification(
                'Statut conducteur rejeté',
                "Votre demande de statut conducteur a été rejetée. Motif : {$driverRequest->motif_rejet}",
            ));
        }

        return new DriverProfileResource($driverRequest);
    }
}
