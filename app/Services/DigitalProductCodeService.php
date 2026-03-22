<?php

namespace App\Services;

use App\Models\DigitalProductCode;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DigitalProductCodeService
{
    /**
     * Add a single plain-text code to the pool for a product.
     * The code is AES-256-CBC encrypted before storage.
     * A SHA-256 hash of the normalised plain code is stored for duplicate detection.
     *
     * Returns null (without throwing) when a duplicate is detected so callers can
     * gracefully count skipped rows.
     */
    public function addToPool(
        int $productId,
        string $plainCode,
        ?string $serialNumber = null,
        ?string $expiryDate = null,
    ): ?DigitalProductCode {
        $normalised = strtolower(trim($plainCode));
        $hash = hash('sha256', $normalised);

        // ── Duplicate detection ──────────────────────────────────────────────
        // 1. Global PIN duplicate (by hash — works without decrypting AES data)
        if (DigitalProductCode::where('code_hash', $hash)->exists()) {
            return null;
        }

        // 2. Serial number duplicate within the same product
        if ($serialNumber !== null && $serialNumber !== '') {
            $cleanSerial = trim($serialNumber);
            if (DigitalProductCode::where('product_id', $productId)
                ->where('serial_number', $cleanSerial)
                ->exists()
            ) {
                return null;
            }
        }
        // ────────────────────────────────────────────────────────────────────

        $record = DigitalProductCode::create([
            'product_id' => $productId,
            'code' => Crypt::encryptString(trim($plainCode)),
            'code_hash' => $hash,
            'serial_number' => $serialNumber ? trim($serialNumber) : null,
            'expiry_date' => $expiryDate ?: null,
            'status' => 'available',
        ]);

        $this->syncStock($productId);

        return $record;
    }

    /**
     * Add many codes for a product in one shot.
     * Each item in $records should be:
     *   ['code' => string, 'serial_number' => ?string, 'expiry_date' => ?string]
     * Passing a plain string is also accepted for backwards compatibility.
     *
     * @param  array<int, string|array{code: string, serial_number?: string|null, expiry_date?: string|null}>  $records
     * @return array{inserted: int, skipped: int}
     */
    public function bulkAddToPool(int $productId, array $records): array
    {
        $inserted = 0;
        $skipped = 0;

        foreach ($records as $record) {
            // Support both plain strings (legacy) and structured arrays
            if (is_string($record)) {
                $plainCode = trim($record);
                $serialNumber = null;
                $expiryDate = null;
            } else {
                $plainCode = trim((string) ($record['code'] ?? ''));
                $serialNumber = isset($record['serial_number']) ? trim((string) $record['serial_number']) : null;
                $expiryDate = $record['expiry_date'] ?? null;
            }

            if ($plainCode === '') {
                $skipped++;

                continue;
            }

            $result = $this->addToPool($productId, $plainCode, $serialNumber, $expiryDate);
            if ($result === null) {
                $skipped++; // duplicate
            } else {
                $inserted++;
            }
        }

        $this->syncStock($productId);

        return ['inserted' => $inserted, 'skipped' => $skipped];
    }

    /**
     * Pick one available code for a product and mark it as reserved.
     * Uses a DB-level lock (SELECT … FOR UPDATE) to prevent race conditions.
     * Codes with a past expiry date are skipped automatically.
     *
     * Returns null if no available code exists.
     */
    public function reserve(int $productId, int $orderId): ?DigitalProductCode
    {
        return DB::transaction(function () use ($productId, $orderId): ?DigitalProductCode {
            /** @var DigitalProductCode|null $record */
            $record = DigitalProductCode::query()
                ->where('product_id', $productId)
                ->where('status', 'available')
                ->where(function ($q): void {
                    $q->whereNull('expiry_date')
                        ->orWhereDate('expiry_date', '>=', now()->toDateString());
                })
                ->lockForUpdate()
                ->first();

            if (! $record) {
                return null;
            }

            $record->update([
                'status' => 'reserved',
                'order_id' => $orderId,
            ]);

            $this->syncStock($productId);

            return $record;
        });
    }

    /**
     * Confirm delivery of a reserved code (payment confirmed).
     * Marks the code as sold and records delivery timestamp.
     */
    public function markSold(int $codeId, int $orderDetailId): void
    {
        DigitalProductCode::query()
            ->where('id', $codeId)
            ->update([
                'status' => 'sold',
                'order_detail_id' => $orderDetailId,
                'assigned_at' => now(),
            ]);
    }

    /**
     * Release a reserved code back to the pool (e.g. payment failed / order cancelled).
     */
    public function release(int $productId, int $orderId): void
    {
        DigitalProductCode::query()
            ->where('product_id', $productId)
            ->where('order_id', $orderId)
            ->whereIn('status', ['reserved'])
            ->update([
                'status' => 'available',
                'order_id' => null,
                'order_detail_id' => null,
                'assigned_at' => null,
            ]);

        $this->syncStock($productId);
    }

    /**
     * Assign codes to all digital order details for a given Order when payment is confirmed.
     * Idempotent — already-assigned details are skipped.
     */
    public function assignCodesForOrder(Order $order): void
    {
        if (! $order->orderDetails) {
            return;
        }

        foreach ($order->orderDetails as $detail) {
            // Skip if already assigned
            if (DigitalProductCode::query()->where('order_detail_id', $detail->id)->exists()) {
                continue;
            }

            $productDetails = json_decode($detail->product_details ?? '{}');
            $productType = $productDetails->product_type ?? null;
            $digitalType = $productDetails->digital_product_type ?? null;

            if ($productType !== 'digital' || $digitalType !== 'ready_product') {
                continue;
            }

            $productId = $detail->product_id ?? ($productDetails->id ?? null);
            if (! $productId) {
                continue;
            }

            try {
                DB::transaction(function () use ($productId, $detail, $order): void {
                    /** @var DigitalProductCode|null $record */
                    $record = DigitalProductCode::query()
                        ->where('product_id', $productId)
                        ->where('status', 'available')
                        ->where(function ($q): void {
                            $q->whereNull('expiry_date')
                                ->orWhereDate('expiry_date', '>=', now()->toDateString());
                        })
                        ->lockForUpdate()
                        ->first();

                    if (! $record) {
                        // No code available — leave for manual fulfilment / re-stock
                        Log::warning('DigitalProductCodeService: No available code in pool', [
                            'product_id' => $productId,
                            'order_id' => $order->id,
                            'order_detail_id' => $detail->id,
                        ]);

                        return;
                    }

                    $record->update([
                        'status' => 'sold',
                        'order_id' => $order->id,
                        'order_detail_id' => $detail->id,
                        'assigned_at' => now(),
                    ]);

                    $this->syncStock($productId);
                });
            } catch (\Throwable $e) {
                Log::error('DigitalProductCodeService: assignCodesForOrder failed', [
                    'order_id' => $order->id,
                    'detail_id' => $detail->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Retrieve the decrypted code for a delivered order detail.
     * Returns null if no code is assigned yet.
     */
    public function getDecryptedCodeForOrderDetail(int $orderDetailId): ?string
    {
        $record = DigitalProductCode::query()
            ->where('order_detail_id', $orderDetailId)
            ->whereIn('status', ['sold', 'reserved'])
            ->first();

        if (! $record) {
            return null;
        }

        return $record->decryptCode();
    }

    /**
     * Sync the product's current_stock to match available, non-expired codes.
     */
    public function syncStock(int $productId): void
    {
        $available = DigitalProductCode::query()
            ->where('product_id', $productId)
            ->where('status', 'available')
            ->where(function ($q): void {
                $q->whereNull('expiry_date')
                    ->orWhereDate('expiry_date', '>=', now()->toDateString());
            })
            ->count();

        Product::query()
            ->where('id', $productId)
            ->update(['current_stock' => $available]);
    }

    /**
     * Mark all codes whose expiry_date is in the past (and status is 'available')
     * as 'expired', then sync stock for affected products.
     * Called by the daily MarkExpiredDigitalCodesCommand.
     *
     * @return int Number of codes marked expired
     */
    public function markExpiredCodes(): int
    {
        // Find all product IDs that will be affected before we update
        $affectedProductIds = DigitalProductCode::query()
            ->pastExpiry()
            ->distinct()
            ->pluck('product_id')
            ->toArray();

        $count = DigitalProductCode::query()
            ->pastExpiry()
            ->update(['status' => 'expired', 'updated_at' => now()]);

        // Sync stock for every impacted product
        foreach ($affectedProductIds as $productId) {
            $this->syncStock($productId);
        }

        return $count;
    }

    /**
     * Get pool statistics for a product.
     *
     * @return array{available: int, reserved: int, sold: int, total: int}
     */
    public function getPoolStats(int $productId): array
    {
        $stats = DigitalProductCode::query()
            ->where('product_id', $productId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'available' => (int) ($stats['available'] ?? 0),
            'reserved' => (int) ($stats['reserved'] ?? 0),
            'sold' => (int) ($stats['sold'] ?? 0),
            'total' => array_sum($stats),
        ];
    }
}
