<?php

namespace App\Exceptions\Inventory;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public static function forProduct(string $product, int $requested, int $available): self
    {
        return new self(
            "Insufficient stock for [{$product}]: requested {$requested}, available {$available}."
        );
    }
}
