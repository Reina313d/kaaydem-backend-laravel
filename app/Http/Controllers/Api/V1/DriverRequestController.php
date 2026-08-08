<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\DriverValidationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\DriverRequestRequest;
use App\Http\Resources\DriverProfileResource;
use App\Models\DriverProfile;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DriverRequestController extends Controller
{
    public function store(DriverRequestRequest $request)
    {
        $user = $request->user();

        if ($user->driverProfile()->exists()) {
            throw ValidationException::withMessages([
                'numero_permis' => ["Une demande de statut conducteur existe deja pour ce compte."],
            ]);
        }

        $profil = DriverProfile::create([
            'user_id' => $user->id,
            ...$request->validated(),
            'statut_validation' => DriverValidationStatus::EnAttente,
        ]);

        return new DriverProfileResource($profil->load('user'));
    }

    public function show(Request $request)
    {
        $profil = $request->user()->driverProfile;

        if (! $profil) {
            return response()->json(['message' => "Aucune demande de statut conducteur trouvee."], 404);
        }

        return new DriverProfileResource($profil);
    }
}
