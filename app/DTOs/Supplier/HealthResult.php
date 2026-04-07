<?php

namespace App\DTOs\Supplier;

/**
 * Health check result from a supplier API.
 */
readonly class HealthResult
{
    /**
     * @param  string  $status  healthy|degraded|down
     * @param  int  $latencyMs  Response time in milliseconds
     * @param  string|null  $message  Human-readable status message
     */
    public function __construct(
        public string $status,
        public int $latencyMs = 0,
        public ?string $message = null,
    ) {}

    public function isHealthy(): bool
    {
        return $this->status === 'healthy';
    }

    public function isDown(): bool
    {
        return $this->status === 'down';
    }
}
