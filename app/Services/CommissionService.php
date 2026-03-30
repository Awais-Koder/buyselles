<?php

namespace App\Services;

use App\Models\Seller;

/**
 * Calculates the admin commission deducted from vendor earnings.
 *
 * Commission is purely a vendor-side deduction — the customer never sees it.
 * Two types are supported:
 *   - percent : classic percentage of the order total (existing behaviour)
 *   - flat    : fixed monetary amount per order
 */
class CommissionService
{
    /**
     * Calculate the admin commission amount for a vendor order.
     *
     * @param  string  $sellerIs  'seller' | 'admin'
     * @param  float  $orderTotal  Pre-tax, pre-fee order total
     */
    public function calculate(string $sellerIs, ?int $sellerId, float $orderTotal): float
    {
        if ($sellerIs !== 'seller') {
            return 0.0;
        }

        [$rate, $type] = $this->resolveRateAndType($sellerId);

        return $this->applyCommission($orderTotal, $rate, $type);
    }

    /**
     * Resolve the commission rate and type for a given seller.
     * Per-seller override takes priority over the global default.
     *
     * @return array{float, string} [rate, type]
     */
    public function resolveRateAndType(?int $sellerId): array
    {
        $seller = $sellerId ? Seller::find($sellerId) : null;

        if ($seller && $seller->sales_commission_percentage !== null) {
            // Per-seller always stores a percentage (legacy column)
            return [(float) $seller->sales_commission_percentage, 'percent'];
        }

        $rate = (float) (getWebConfig(name: 'sales_commission') ?? 0);
        $type = getWebConfig(name: 'sales_commission_type') ?? 'percent';

        return [$rate, $type];
    }

    /**
     * Apply the commission formula given rate and type.
     *
     * @param  string  $type  'percent' | 'flat'
     */
    public function applyCommission(float $orderTotal, float $rate, string $type): float
    {
        if ($type === 'flat') {
            return round($rate, 2);
        }

        return round(($orderTotal / 100) * $rate, 2);
    }
}
