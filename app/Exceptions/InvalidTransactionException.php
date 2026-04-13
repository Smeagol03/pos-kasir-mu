<?php

namespace App\Exceptions;

use Exception;

class InvalidTransactionException extends Exception
{
    public function __construct(string $message = 'Invalid transaction')
    {
        parent::__construct($message);
    }
}