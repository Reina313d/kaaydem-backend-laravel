<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case EnAttente = 'en_attente';
    case Confirmee = 'confirmee';
    case Terminee = 'terminee';
    case Annulee = 'annulee';
    case Refusee = 'refusee';

    public function label(): string
    {
        return match ($this) {
            self::EnAttente => 'En attente',
            self::Confirmee => 'Confirmée',
            self::Terminee => 'Terminée',
            self::Annulee => 'Annulée',
            self::Refusee => 'Refusée',
        };
    }

    /**
     * Transitions autorisees depuis ce statut.
     *
     * @return ReservationStatus[]
     */
    public function transitionsAutorisees(): array
    {
        return match ($this) {
            self::EnAttente => [self::Confirmee, self::Refusee, self::Annulee],
            self::Confirmee => [self::Terminee, self::Annulee],
            default => [],
        };
    }
}
