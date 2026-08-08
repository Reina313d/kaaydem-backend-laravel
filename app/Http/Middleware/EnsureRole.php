<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de controle d'acces par role.
 * Usage: ->middleware('role:admin') ou ->middleware('role:conducteur,admin')
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Authentification requise.'], 401);
        }

        if (in_array('admin', $roles) && $user->is_admin) {
            return $next($request);
        }

        if (in_array('conducteur', $roles) && $user->estConducteurValide()) {
            return $next($request);
        }

        if (in_array('passager', $roles)) {
            return $next($request);
        }

        return response()->json([
            'message' => "Vous n'avez pas les droits necessaires pour cette action.",
        ], 403);
    }
}
