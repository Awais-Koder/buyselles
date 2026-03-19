<?php

namespace App\Services;

class VendorWalletService
{
    /**
     * @return int[]|string[]
     */
    public function getVendorWalletData(int|string $totalEarning, int|string $pendingWithdraw): array
    {
        return [
            'total_earning' => $totalEarning,
            'pending_withdraw' => $pendingWithdraw,
        ];
    }
}
