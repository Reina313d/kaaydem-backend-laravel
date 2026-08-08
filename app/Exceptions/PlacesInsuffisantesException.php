<?php

namespace App\Exceptions;

use Exception;

class PlacesInsuffisantesException extends Exception
{
    public function __construct(string $message = "Places insuffisantes pour cette reservation.")
    {
        parent::__construct($message);
    }
}
