<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'trip_id',
        'passenger_id',
        'nombre_places',
        'statut',
        'historique_transitions',
    ];

    protected function casts(): array
    {
        return [
            'statut' => ReservationStatus::class,
            'historique_transitions' => 'array',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'passenger_id');
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function ajouterTransition(ReservationStatus $nouveauStatut, ?int $acteurId): void
    {
        $historique = $this->historique_transitions ?? [];
        $historique[] = [
            'de' => $this->statut->value,
            'vers' => $nouveauStatut->value,
            'acteur_id' => $acteurId,
            'date' => now()->toIso8601String(),
        ];

        $this->historique_transitions = $historique;
        $this->statut = $nouveauStatut;
    }
}
