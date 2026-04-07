<?php

namespace App\DTOs\Supplier;

/**
 * Parsed and verified webhook payload from a supplier.
 */
readonly class WebhookResult
{
    /**
     * @param  string  $type  order_fulfilled|order_failed|stock_update|unknown
     * @param  string[]  $codes  Plain-text codes received via webhook
     * @param  bool  $verified  Whether the webhook signature/HMAC was valid
     */
    public function __construct(
        public string $type,
        public ?string $supplierOrderId = null,
        public array $codes = [],
        public string $status = 'unknown',
        public bool $verified = false,
        public array $rawPayload = [],
    ) {}

    public function isVerified(): bool
    {
        return $this->verified;
    }

    public function hasCodes(): bool
    {
        return ! empty($this->codes);
    }
}
