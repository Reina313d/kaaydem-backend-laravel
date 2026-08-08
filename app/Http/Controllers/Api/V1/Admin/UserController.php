<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReportResource;
use App\Http\Resources\UserResource;
use App\Models\Report;
use App\Models\User;
use App\Notifications\KaaydemNotification;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with('driverProfile');

        if ($request->filled('recherche')) {
            $recherche = $request->query('recherche');
            $query->where(fn ($q) => $q
                ->where('nom', 'like', "%{$recherche}%")
                ->orWhere('email', 'like', "%{$recherche}%"));
        }

        $utilisateurs = $query->latest()->paginate($request->query('par_page', 15));

        return UserResource::collection($utilisateurs);
    }

    public function toggleActive(User $user)
    {
        $user->update(['actif' => ! $user->actif]);

        return new UserResource($user);
    }

    public function reports(Request $request)
    {
        $reports = Report::with(['auteur', 'utilisateurSignale'])
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->query('statut')))
            ->latest()
            ->paginate($request->query('par_page', 15));

        return ReportResource::collection($reports);
    }

    public function updateReport(Request $request, Report $report)
    {
        $request->validate([
            'statut' => ['required', 'in:ouvert,en_cours_traitement,resolu,rejete'],
        ]);

        $report->update(['statut' => $request->input('statut')]);
        $report->loadMissing(['auteur', 'utilisateurSignale']);

        if (in_array($request->input('statut'), ['resolu', 'rejete'], true)) {
            $report->auteur->notify(new KaaydemNotification(
                'Signalement traité',
                "Votre signalement concernant {$report->utilisateurSignale->nom} a ete traite par l'administration.",
            ));
        }

        return new ReportResource($report);
    }
}
