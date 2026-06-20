<?php

namespace App\Exceptions;

use Exception;

class SpotExhaustedException extends Exception
{
    protected $message = 'Vagas esgotadas no momento.';
}
