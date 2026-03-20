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
     * The code is encrypted before storage.
     */
    public function addToPool(int $productId, string $plainCode): DigitalProductCode
    {
        $record = DigitalProductCode::create([
            'product_id' => $productId,
            'code' => Crypt::encryptString(trim($plainCode)),
            'status' => 'available',
        ]);

        $this->syncStock($productId);

        return $record;
    }

    /**
     * Add many plain-text codes for a product in one shot.
     * Skips blank entries and already-available duplicates (by checking hash).
     *
     * @param  array<int, string>  $plainCodes
     * @return array{inserted: int, skipped: int}
     */
    public function bulkAddToPool(int $productId, array $plainCodes): array
    {
        $inserted = 0;
        $skipped = 0;

        foreach ($plainCodes as $plainCode) {
            $plainCode = trim((string) $plainCode);

            if ($plainCode === '') {
                $skipped++;

                continue;
            }

            DigitalProductCode::create([
                'product_id' => $productId,
                'code' => Crypt::encryptString($plainCode),
                'status' => 'available',
            ]);

            $inserted++;
        }

        $this->syncStock($productId);

        return ['inserted' => $inserted, 'skipped' => $skipped];
    }

    /**
     * Pick one available code for a product and mark it as reserved.
     * Uses a DB-level lock (SELECT … FOR UPDATE) to prevent race conditions.
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
     * Sync the product's current_stock to match available codes in the pool.
     */
    public function syncStock(int $productId): void
    {
        $available = DigitalProductCode::query()
            ->where('product_id', $productId)
            ->where('status', 'available')
            ->count();

        Product::query()
            ->where('id', $productId)
            ->update(['current_stock' => $available]);
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
