<?php

namespace App\DTOs\Supplier;

/**
 * Stock availability result from a supplier.
 */
readonly class StockResult
{
    public function __construct(
        public int $available,
        public float $price,
        public string $currency = 'USD',
        public array $rawData = [],
    ) {}
}
