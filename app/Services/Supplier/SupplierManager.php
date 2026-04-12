<?php

namespace App\Services\Supplier;

use App\Contracts\SupplierDriverInterface;
use App\DTOs\Supplier\BalanceResult;
use App\Models\DigitalProductCode;
use App\Models\Order;
use App\Models\Product;
use App\Models\SupplierApi;
use App\Models\SupplierOrder;
use App\Models\SupplierProductMapping;
use App\Services\DigitalProductCodeService;
use App\Services\Supplier\Drivers\BambooDriver;
use App\Services\Supplier\Drivers\GenericRestDriver;
use App\Services\Supplier\Drivers\KinguinDriver;
use App\Services\Supplier\Drivers\ReloadlyDriver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SupplierManager
{
    /**
     * Map of driver keys to their implementing classes.
     * Adding a new supplier = add one entry here + one driver class.
     *
     * @var array<string, class-string<SupplierDriverInterface>>
     */
    protected array $drivers = [
        'generic_rest' => GenericRestDriver::class,
        'reloadly' => ReloadlyDriver::class,
        'kinguin' => KinguinDriver::class,
        'bamboo' => BambooDriver::class,
    ];

    public function __construct(
        private readonly DigitalProductCodeService $codeService,
        private readonly SupplierApiLogger $logger,
        private readonly SupplierRateLimiter $rateLimiter,
    ) {}

    /**
     * Resolve and configure a driver instance for the given supplier.
     */
    public function driver(SupplierApi $supplier): SupplierDriverInterface
    {
        $driverClass = $this->drivers[$supplier->driver] ?? null;

        if (! $driverClass) {
            throw new \InvalidArgumentException("Unknown supplier driver: {$supplier->driver}");
        }

        /** @var SupplierDriverInterface $instance */
        $instance = app($driverClass);

        return $instance->configure($supplier);
    }

    /**
     * Get all registered driver keys.
     *
     * @return string[]
     */
    public function getAvailableDrivers(): array
    {
        return array_keys($this->drivers);
    }

    /**
     * Fetch codes from suppliers and add them to the digital code pool.
     * Tries suppliers in priority order (fallback chain).
     *
     * @return array{inserted: int, supplier_id: int|null, supplier_order_id: int|null}
     */
    public function fetchAndStockCodes(Product $product, int $quantity): array
    {
        $mappings = SupplierProductMapping::where('product_id', $product->id)
            ->active()
            ->byPriority()
            ->with('supplierApi')
            ->get();

        foreach ($mappings as $mapping) {
            $supplier = $mapping->supplierApi;

            if (! $supplier->is_active || $supplier->isDown()) {
                continue;
            }

            if (! $this->rateLimiter->attempt($supplier->id, $supplier->rate_limit_per_minute)) {
                Log::warning('SupplierManager: rate limited', [
                    'supplier_id' => $supplier->id,
                    'product_id' => $product->id,
                ]);

                continue;
            }

            try {
                $result = $this->placeSupplierOrder($supplier, $mapping, $quantity);

                if ($result['inserted'] > 0) {
                    return $result;
                }
            } catch (\Throwable $e) {
                Log::error('SupplierManager: fetchAndStockCodes failed for supplier', [
                    'supplier_id' => $supplier->id,
                    'product_id' => $product->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return ['inserted' => 0, 'supplier_id' => null, 'supplier_order_id' => null];
    }

    /**
     * Fulfill an order by fetching codes from suppliers on-demand.
     * Called when local pool is exhausted and product has supplier mappings.
     *
     * @return bool True if codes were obtained and assigned
     */
    public function fulfillOrder(Order $order): bool
    {
        $order->loadMissing('orderDetails');

        $anyFulfilled = false;

        foreach ($order->orderDetails as $detail) {
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

            // Check how many codes are still needed
            $alreadyAssigned = DigitalProductCode::where('order_detail_id', $detail->id)
                ->where('status', 'sold')
                ->count();

            $needed = max(0, (int) $detail->qty - $alreadyAssigned);

            if ($needed <= 0) {
                continue;
            }

            // Check if product has supplier mappings
            $hasSuppliers = SupplierProductMapping::where('product_id', $productId)
                ->active()
                ->exists();

            if (! $hasSuppliers) {
                continue;
            }

            $product = Product::find($productId);
            if (! $product) {
                continue;
            }

            $result = $this->fetchAndStockCodes($product, $needed);

            // Always link supplier order to platform order (even for async/V1 where codes come via webhook)
            if ($result['supplier_order_id']) {
                SupplierOrder::where('id', $result['supplier_order_id'])->update([
                    'order_id' => $order->id,
                    'order_detail_id' => $detail->id,
                ]);
            }

            if ($result['inserted'] > 0) {
                $anyFulfilled = true;
            }
        }

        if ($anyFulfilled) {
            // Re-run assignment now that new codes are in the pool
            $this->codeService->assignAndNotify($order);
        }

        return $anyFulfilled;
    }

    /**
     * Sync stock for a specific product-supplier mapping.
     * Checks remote stock, auto-restocks if below threshold.
     */
    public function syncStock(SupplierProductMapping $mapping): void
    {
        $supplier = $mapping->supplierApi;

        if (! $supplier->is_active || $supplier->isDown()) {
            return;
        }

        if (! $this->rateLimiter->attempt($supplier->id, $supplier->rate_limit_per_minute)) {
            return;
        }

        $logId = $this->logger->logRequest(
            supplierApiId: $supplier->id,
            action: 'fetch_stock',
            endpoint: $supplier->base_url,
            method: 'GET',
        );

        $startTime = microtime(true);

        try {
            $driver = $this->driver($supplier);
            $stockResult = $driver->fetchStock($mapping->supplier_product_id);

            $this->logger->logResponse(
                logId: $logId,
                httpStatusCode: 200,
                responsePayload: [
                    'available' => $stockResult->available,
                    'price' => $stockResult->price,
                    'currency' => $stockResult->currency,
                ],
                responseTimeMs: (int) ((microtime(true) - $startTime) * 1000),
            );

            // Update cost price from supplier if needed
            if ($stockResult->price > 0 && $stockResult->price != $mapping->cost_price) {
                $mapping->update([
                    'cost_price' => $stockResult->price,
                    'cost_currency' => $stockResult->currency,
                ]);
            }

            // Always sync the product's selling price when manual stock is depleted
            $this->codeService->applyApiPriceIfManualDepleted($mapping->product_id);

            $mapping->update(['last_synced_at' => now()]);

            // Auto-restock if below threshold
            if ($mapping->auto_restock) {
                $localStock = DigitalProductCode::where('product_id', $mapping->product_id)
                    ->available()
                    ->count();

                if ($localStock < $mapping->min_stock_threshold && $stockResult->available > 0) {
                    $qty = min($mapping->max_restock_qty, $stockResult->available);
                    $this->placeSupplierOrder($supplier, $mapping, $qty);
                }
            }
        } catch (\Throwable $e) {
            $this->logger->logError(
                logId: $logId,
                errorMessage: $e->getMessage(),
                responseTimeMs: (int) ((microtime(true) - $startTime) * 1000),
            );
        }
    }

    /**
     * Process an incoming webhook from a supplier.
     */
    public function handleWebhook(SupplierApi $supplier, \Illuminate\Http\Request $request): void
    {
        $logId = $this->logger->logRequest(
            supplierApiId: $supplier->id,
            action: 'webhook',
            endpoint: $request->fullUrl(),
            method: $request->method(),
            requestPayload: $request->all(),
        );

        $startTime = microtime(true);

        try {
            $driver = $this->driver($supplier);
            $result = $driver->parseWebhook($request);

            if (! $result->isVerified()) {
                $this->logger->logError($logId, 'Webhook signature verification failed');

                return;
            }

            $this->logger->logResponse(
                logId: $logId,
                httpStatusCode: 200,
                responsePayload: ['type' => $result->type, 'status' => $result->status, 'codes_count' => count($result->codes)],
                responseTimeMs: (int) ((microtime(true) - $startTime) * 1000),
            );

            // Process codes from webhook
            if ($result->supplierOrderId) {
                $supplierOrder = SupplierOrder::where('supplier_api_id', $supplier->id)
                    ->where('supplier_order_id', $result->supplierOrderId)
                    ->first();

                if ($supplierOrder) {
                    if ($result->hasCodes()) {
                        $this->processReceivedCodes(
                            supplierOrder: $supplierOrder,
                            mapping: $supplierOrder->productMapping,
                            codes: $result->codes,
                        );

                        // If this supplier order is linked to a platform order, assign codes to customer
                        if ($supplierOrder->order_id) {
                            $order = Order::find($supplierOrder->order_id);
                            if ($order) {
                                $this->codeService->assignAndNotify($order);
                            }
                        }
                    } elseif ($result->type === 'order_failed') {
                        $supplierOrder->update(['status' => 'failed']);
                    }
                }
            }
        } catch (\Throwable $e) {
            $this->logger->logError(
                logId: $logId,
                errorMessage: $e->getMessage(),
                responseTimeMs: (int) ((microtime(true) - $startTime) * 1000),
            );
        }
    }

    /**
     * Place an order with a supplier and process received codes.
     *
     * @return array{inserted: int, supplier_id: int, supplier_order_id: int|null}
     */
    private function placeSupplierOrder(
        SupplierApi $supplier,
        SupplierProductMapping $mapping,
        int $quantity,
    ): array {
        $logId = $this->logger->logRequest(
            supplierApiId: $supplier->id,
            action: 'place_order',
            endpoint: $supplier->base_url,
            method: 'POST',
            requestPayload: [
                'supplier_product_id' => $mapping->supplier_product_id,
                'quantity' => $quantity,
            ],
        );

        $startTime = microtime(true);

        try {
            $driver = $this->driver($supplier);
            $result = $driver->placeOrder($mapping->supplier_product_id, $quantity, (float) $mapping->cost_price);

            $this->logger->logResponse(
                logId: $logId,
                httpStatusCode: 200,
                responsePayload: [
                    'supplier_order_id' => $result->supplierOrderId,
                    'status' => $result->status,
                    'codes_count' => count($result->codes),
                ],
                responseTimeMs: (int) ((microtime(true) - $startTime) * 1000),
            );

            // Create supplier order record
            $supplierOrder = SupplierOrder::create([
                'supplier_api_id' => $supplier->id,
                'supplier_product_mapping_id' => $mapping->id,
                'supplier_order_id' => $result->supplierOrderId,
                'quantity' => $quantity,
                'cost_per_unit' => $mapping->cost_price,
                'total_cost' => $mapping->cost_price * $quantity,
                'cost_currency' => $mapping->cost_currency,
                'status' => $result->status,
            ]);

            $inserted = 0;

            if ($result->hasCodes()) {
                $inserted = $this->processReceivedCodes($supplierOrder, $mapping, $result->codes);
            }

            return [
                'inserted' => $inserted,
                'supplier_id' => $supplier->id,
                'supplier_order_id' => $supplierOrder->id,
            ];
        } catch (\Throwable $e) {
            $this->logger->logError(
                logId: $logId,
                errorMessage: $e->getMessage(),
                responseTimeMs: (int) ((microtime(true) - $startTime) * 1000),
            );

            throw $e;
        }
    }

    /**
     * Fetch balance/credit from all active suppliers. Cached for 15 minutes.
     *
     * @return array<int, array{id: int, name: string, driver: string, balance: BalanceResult}>
     */
    public function getSupplierBalances(): array
    {
        return Cache::remember('supplier_api_balances', 900, function () {
            $suppliers = SupplierApi::where('is_active', true)->get();

            return $suppliers->map(function (SupplierApi $supplier) {
                try {
                    $balance = $this->driver($supplier)->getBalance();
                } catch (\Throwable) {
                    $balance = BalanceResult::unsupported();
                }

                return [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'driver' => $supplier->driver,
                    'balance' => $balance,
                ];
            })->all();
        });
    }

    /**
     * Process received codes: encrypt into pool via DigitalProductCodeService, update supplier order.
     */
    private function processReceivedCodes(
        SupplierOrder $supplierOrder,
        SupplierProductMapping $mapping,
        array $codes,
    ): int {
        $supplierOrder->setEncryptedCodes($codes);

        $bulkResult = $this->codeService->bulkAddToPool(
            productId: $mapping->product_id,
            records: $codes,
            source: 'supplier_api',
        );

        $supplierOrder->update([
            'status' => $bulkResult['inserted'] >= $supplierOrder->quantity ? 'fulfilled' : 'partial',
            'fulfilled_at' => $bulkResult['inserted'] > 0 ? now() : null,
            'codes_received' => $supplierOrder->codes_received,
        ]);

        $mapping->update(['last_synced_at' => now()]);

        // If all manual stock is depleted, switch the product price to the API-based price
        if ($bulkResult['inserted'] > 0) {
            $this->codeService->applyApiPriceIfManualDepleted($mapping->product_id);
        }

        return $bulkResult['inserted'];
    }
}
