<?php

namespace App\Services\Supplier;

use Illuminate\Support\Facades\Cache;

class SupplierRateLimiter
{
    /**
     * Attempt to consume one request slot for the given supplier.
     * Returns true if the request is allowed, false if rate-limited.
     */
    public function attempt(int $supplierApiId, int $maxPerMinute): bool
    {
        $key = "supplier_rate_limit:{$supplierApiId}";

        return Cache::lock("{$key}:lock", 5)->block(3, function () use ($key, $maxPerMinute): bool {
            $current = (int) Cache::get($key, 0);

            if ($current >= $maxPerMinute) {
                return false;
            }

            if ($current === 0) {
                Cache::put($key, 1, 60);
            } else {
                Cache::increment($key);
            }

            return true;
        });
    }

    /**
     * Get the remaining request slots for a supplier in the current window.
     */
    public function remaining(int $supplierApiId, int $maxPerMinute): int
    {
        $current = (int) Cache::get("supplier_rate_limit:{$supplierApiId}", 0);

        return max(0, $maxPerMinute - $current);
    }

    /**
     * Reset the rate limit counter for a supplier.
     */
    public function reset(int $supplierApiId): void
    {
        Cache::forget("supplier_rate_limit:{$supplierApiId}");
    }
}
