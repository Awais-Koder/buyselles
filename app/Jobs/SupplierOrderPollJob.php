<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\SupplierOrder;
use App\Services\DigitalProductCodeService;
use App\Services\Supplier\SupplierManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Poll a supplier's GET order endpoint to fetch codes for an async order.
 *
 * Dispatched after BambooDriver::placeOrder() returns status='processing'
 * (V1 async flow where codes are not returned inline).
 * Replaces webhook dependency — polls until codes arrive or retries exhausted.
 */
class SupplierOrderPollJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $timeout = 60;

    /**
     * @return int[]
     */
    public function backoff(): array
    {
        return [30, 60, 120, 180, 300];
    }

    public function __construct(
        public readonly int $supplierOrderId,
    ) {}

    public function handle(SupplierManager $manager, DigitalProductCodeService $codeService): void
    {
        $supplierOrder = SupplierOrder::with(['supplierApi', 'productMapping'])->find($this->supplierOrderId);

        if (! $supplierOrder) {
            Log::warning('SupplierOrderPollJob: SupplierOrder not found', [
                'supplier_order_id' => $this->supplierOrderId,
            ]);

            return;
        }

        // Already fulfilled — skip
        if (in_array($supplierOrder->status, ['fulfilled', 'failed', 'refunded'])) {
            Log::info('SupplierOrderPollJob: already resolved, skipping', [
                'id' => $supplierOrder->id,
                'status' => $supplierOrder->status,
            ]);

            return;
        }

        $supplier = $supplierOrder->supplierApi;
        $mapping = $supplierOrder->productMapping;

        if (! $supplier || ! $mapping) {
            Log::error('SupplierOrderPollJob: missing supplier or mapping', [
                'id' => $supplierOrder->id,
            ]);

            return;
        }

        try {
            $driver = $manager->driver($supplier);
            $result = $driver->getOrderStatus($supplierOrder->supplier_order_id);

            Log::info('SupplierOrderPollJob: polled supplier', [
                'id' => $supplierOrder->id,
                'supplier_order_id' => $supplierOrder->supplier_order_id,
                'status' => $result->status,
                'codes_count' => count($result->codes),
                'attempt' => $this->attempts(),
            ]);

            if ($result->status === 'failed') {
                $supplierOrder->update([
                    'status' => 'failed',
                    'failed_reason' => 'Supplier reported order failed',
                    'attempt_count' => $this->attempts(),
                ]);

                return;
            }

            if ($result->hasCodes()) {
                $this->processAndAssign($supplierOrder, $mapping, $result->codes, $codeService, $manager);

                return;
            }

            // Still processing — let the retry mechanism handle it
            if ($result->status === 'processing') {
                $supplierOrder->update(['attempt_count' => $this->attempts()]);
                $this->release($this->backoff()[$this->attempts() - 1] ?? 300);
            }
        } catch (\Throwable $e) {
            Log::error('SupplierOrderPollJob: poll failed', [
                'id' => $supplierOrder->id,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function processAndAssign(
        SupplierOrder $supplierOrder,
        \App\Models\SupplierProductMapping $mapping,
        array $codes,
        DigitalProductCodeService $codeService,
        SupplierManager $manager,
    ): void {
        // Encrypt codes into SupplierOrder
        $plainCodes = array_map(fn ($c) => is_array($c) ? ($c['code'] ?? '') : $c, $codes);
        $supplierOrder->setEncryptedCodes($plainCodes);

        // Add codes to pool via code service
        $bulkResult = $codeService->bulkAddToPool(
            productId: $mapping->product_id,
            records: $codes,
            source: 'supplier_api',
        );

        $supplierOrder->update([
            'status' => $bulkResult['inserted'] >= $supplierOrder->quantity ? 'fulfilled' : 'partial',
            'fulfilled_at' => $bulkResult['inserted'] > 0 ? now() : null,
            'codes_received' => $supplierOrder->codes_received,
            'attempt_count' => $this->attempts(),
        ]);

        $mapping->update(['last_synced_at' => now()]);

        if ($bulkResult['inserted'] > 0) {
            $codeService->applyApiPriceIfManualDepleted($mapping->product_id);
        }

        Log::info('SupplierOrderPollJob: codes processed', [
            'id' => $supplierOrder->id,
            'inserted' => $bulkResult['inserted'],
            'skipped' => $bulkResult['skipped'],
        ]);

        // If linked to a platform order, assign codes and send email
        if ($supplierOrder->order_id) {
            $order = Order::find($supplierOrder->order_id);
            if ($order && $order->payment_status === 'paid') {
                $codeService->assignAndNotify($order);

                Log::info('SupplierOrderPollJob: codes assigned and customer notified', [
                    'supplier_order_id' => $supplierOrder->id,
                    'order_id' => $order->id,
                ]);
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::critical('SupplierOrderPollJob: all retries exhausted', [
            'supplier_order_id' => $this->supplierOrderId,
            'error' => $exception->getMessage(),
        ]);

        SupplierOrder::where('id', $this->supplierOrderId)->update([
            'status' => 'failed',
            'failed_reason' => 'Poll retries exhausted: '.$exception->getMessage(),
        ]);
    }
}
