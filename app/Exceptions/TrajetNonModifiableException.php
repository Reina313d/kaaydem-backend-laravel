<?php

namespace App\Exceptions;

use Exception;

class TrajetNonModifiableException extends Exception
{
    public function __construct(string $message = "Ce trajet ne peut plus etre modifie ou annule car des reservations sont deja confirmees.")
    {
        parent::__construct($message);
    }
}
