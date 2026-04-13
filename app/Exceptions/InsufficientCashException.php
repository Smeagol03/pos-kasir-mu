<?php

namespace App\Exceptions;

use Exception;

class InsufficientCashException extends Exception
{
    public function __construct(int $requiredTotal, int $providedCash)
    {
        $message = 'Uang tidak cukup. Total: Rp '.number_format($requiredTotal, 0, ',', '.').' | Diberikan: Rp '.number_format($providedCash, 0, ',', '.');
        parent::__construct($message);
    }
}
