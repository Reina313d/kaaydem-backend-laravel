<?php

namespace App\Exceptions;

use Exception;

class CreneauChevauchantException extends Exception
{
    public function __construct(string $message = "Vous avez deja une reservation sur un trajet qui chevauche ce creneau.")
    {
        parent::__construct($message);
    }
}
