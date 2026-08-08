<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return new UserResource($request->user()->load('driverProfile'));
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $donnees = $request->safe()->except(['photo', 'password', 'password_confirmation']);

        if ($request->filled('password')) {
            $donnees['password'] = Hash::make($request->validated('password'));
        }

        if ($request->hasFile('photo')) {
            $chemin = $request->file('photo')->store('profils', 'public');
            $donnees['photo'] = Storage::url($chemin);
        }

        $user->update($donnees);

        return new UserResource($user->fresh('driverProfile'));
    }
}
