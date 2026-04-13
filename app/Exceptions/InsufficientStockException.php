<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    public function __construct(string $productName, int $availableStock, int $requestedQuantity)
    {
        $message = "Insufficient stock for '{$productName}'. Available: {$availableStock}, Requested: {$requestedQuantity}";
        parent::__construct($message);
    }
}