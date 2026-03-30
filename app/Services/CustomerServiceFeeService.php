<?php

namespace App\Services;

/**
 * Calculates the customer-facing service fee shown at checkout.
 *
 * The service fee is charged to the customer on top of the order total
 * and is displayed on the checkout page as "Service Fee".
 * Two types are supported:
 *   - percent : percentage of the order total (subtotal – discounts + shipping + tax)
 *   - flat    : fixed monetary amount per order
 *
 * Settings stored in business_settings:
 *   customer_service_fee        → numeric value
 *   customer_service_fee_type   → 'percent' | 'flat'
 *   customer_service_fee_status → 0 | 1  (enable/disable)
 */
class CustomerServiceFeeService
{
    /**
     * Returns whether the service fee is enabled globally.
     */
    public function isEnabled(): bool
    {
        return (bool) (getWebConfig(name: 'customer_service_fee_status') ?? 0);
    }

    /**
     * Returns the configured fee rate.
     */
    public function getRate(): float
    {
        return (float) (getWebConfig(name: 'customer_service_fee') ?? 0);
    }

    /**
     * Returns the configured fee type: 'percent' or 'flat'.
     */
    public function getType(): string
    {
        return getWebConfig(name: 'customer_service_fee_type') ?? 'percent';
    }

    /**
     * Calculate the service fee amount for a given order total.
     *
     * @param  float  $orderTotal  Subtotal after discounts, before service fee
     */
    public function calculate(float $orderTotal): float
    {
        if (! $this->isEnabled()) {
            return 0.0;
        }

        $rate = $this->getRate();
        $type = $this->getType();

        if ($rate <= 0) {
            return 0.0;
        }

        if ($type === 'flat') {
            return round($rate, 2);
        }

        return round(($orderTotal / 100) * $rate, 2);
    }
}
