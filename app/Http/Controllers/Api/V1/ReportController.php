<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $donnees = $request->validate([
            // "different:auteur_id" ne fonctionne pas ici car auteur_id n'est pas un champ
            // du formulaire : on interdit explicitement l'auto-signalement avec Rule::notIn.
            'utilisateur_signale_id' => [
                'required',
                'exists:users,id',
                Rule::notIn([$request->user()->id]),
            ],
            'trip_id' => ['nullable', 'exists:trips,id'],
            'motif' => ['required', 'string', 'max:1000'],
        ], [
            'utilisateur_signale_id.not_in' => "Vous ne pouvez pas vous signaler vous-meme.",
        ]);

        $report = Report::create([
            'auteur_id' => $request->user()->id,
            'utilisateur_signale_id' => $donnees['utilisateur_signale_id'],
            'trip_id' => $donnees['trip_id'] ?? null,
            'motif' => $donnees['motif'],
            // On fixe explicitement la valeur plutot que de compter sur le defaut SQL de
            // la colonne : sans cela, l'objet en memoire garde "statut" a null juste apres
            // create() (le defaut n'est applique qu'en base, pas rechargee automatiquement),
            // ce qui fait planter ReportResource sur "$this->statut->value".
            'statut' => 'ouvert',
        ]);

        return new ReportResource($report->load(['auteur', 'utilisateurSignale']));
    }
}
