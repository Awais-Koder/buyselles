<?php

namespace App\DTOs\Supplier;

/**
 * Result of placing an order or checking order status with a supplier.
 */
readonly class SupplierOrderResult
{
    /**
     * @param  string|null  $supplierOrderId  Supplier's order reference
     * @param  string  $status  pending|processing|fulfilled|partial|failed|refunded
     * @param  string[]  $codes  Plain-text codes received from supplier
     * @param  array  $rawResponse  Original response for logging
     */
    public function __construct(
        public ?string $supplierOrderId,
        public string $status,
        public array $codes = [],
        public array $rawResponse = [],
    ) {}

    public function isFulfilled(): bool
    {
        return $this->status === 'fulfilled';
    }

    public function hasCodes(): bool
    {
        return ! empty($this->codes);
    }
}
