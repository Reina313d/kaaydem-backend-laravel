<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'nom' => $request->validated('nom'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'telephone' => $request->validated('telephone'),
            'campus' => $request->validated('campus'),
        ]);

        $token = $user->createToken('kaaydem-spa')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ["Les identifiants fournis sont incorrects."],
            ]);
        }

        if (! $user->actif) {
            throw ValidationException::withMessages([
                'email' => ["Ce compte a ete desactive. Contactez l'administration."],
            ]);
        }

        $token = $user->createToken('kaaydem-spa')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user->load('driverProfile')),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Deconnexion reussie.']);
    }
}
