<?php

namespace App\Models;

use App\Enums\DriverValidationStatus;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nom',
        'email',
        'password',
        'telephone',
        'campus',
        'photo',
        'is_admin',
        'actif',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'actif' => 'boolean',
        ];
    }

    public function driverProfile(): HasOne
    {
        return $this->hasOne(DriverProfile::class);
    }

    public function trajetsConduits(): HasMany
    {
        return $this->hasMany(Trip::class, 'driver_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'passenger_id');
    }

    public function signalementsEmis(): HasMany
    {
        return $this->hasMany(Report::class, 'auteur_id');
    }

    public function signalementsRecus(): HasMany
    {
        return $this->hasMany(Report::class, 'utilisateur_signale_id');
    }

    public function estConducteurValide(): bool
    {
        return $this->driverProfile?->statut_validation === DriverValidationStatus::Valide;
    }

    public function noteMoyenneConducteur(): ?float
    {
        $moyenne = Review::whereHas('reservation.trip', fn ($q) => $q->where('driver_id', $this->id))
            ->avg('note');

        return $moyenne !== null ? round($moyenne, 2) : null;
    }

    public function nombreAvisConducteur(): int
    {
        return Review::whereHas('reservation.trip', fn ($q) => $q->where('driver_id', $this->id))
            ->count();
    }
}
