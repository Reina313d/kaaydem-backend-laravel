<?php

namespace App\Enums;

enum DriverValidationStatus: string
{
    case EnAttente = 'en_attente';
    case Valide = 'valide';
    case Rejete = 'rejete';
}
