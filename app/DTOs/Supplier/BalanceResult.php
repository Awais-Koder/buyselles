<?php

namespace App\DTOs\Supplier;

/**
 * Balance / credit result from a supplier API.
 */
readonly class BalanceResult
{
    /**
     * @param  bool  $supported  Whether this driver supports balance queries
     * @param  float  $balance  Current balance / credit amount
     * @param  string  $currency  Currency code (e.g. USD)
     * @param  string|null  $message  Human-readable message or error reason
     */
    public function __construct(
        public bool $supported,
        public float $balance = 0.0,
        public string $currency = 'USD',
        public ?string $message = null,
    ) {}

    public static function unsupported(): self
    {
        return new self(supported: false, message: 'Balance enquiry not supported by this driver');
    }
}
