<?php

namespace App\Enums;

enum TripStatus: string
{
    case Publie = 'publie';
    case EnCours = 'en_cours';
    case Termine = 'termine';
    case Annule = 'annule';

    public function label(): string
    {
        return match ($this) {
            self::Publie => 'Publié',
            self::EnCours => 'En cours',
            self::Termine => 'Terminé',
            self::Annule => 'Annulé',
        };
    }
}
