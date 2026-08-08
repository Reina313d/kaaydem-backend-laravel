<?php

namespace App\Models;

use App\Enums\TripStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trip extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'driver_id',
        'ville_depart',
        'ville_arrivee',
        'points_arret',
        'date_heure_depart',
        'places_totales',
        'places_disponibles',
        'prix_place',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'points_arret' => 'array',
            'date_heure_depart' => 'datetime',
            'statut' => TripStatus::class,
        ];
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Le trajet est-il modifiable/annulable ? (aucune reservation confirmee)
     */
    public function estModifiable(): bool
    {
        return ! $this->reservations()
            ->whereIn('statut', ['confirmee', 'terminee'])
            ->exists();
    }

    public function scopeRecherche($query, ?string $depart, ?string $arrivee, ?string $date)
    {
        return $query
            ->when($depart, fn ($q) => $q->where('ville_depart', 'like', "%{$depart}%"))
            ->when($arrivee, fn ($q) => $q->where('ville_arrivee', 'like', "%{$arrivee}%"))
            ->when($date, fn ($q) => $q->whereDate('date_heure_depart', $date));
    }
}
