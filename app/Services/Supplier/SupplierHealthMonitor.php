<?php

namespace App\Services\Supplier;

use App\DTOs\Supplier\HealthResult;
use App\Models\SupplierApi;
use Illuminate\Support\Facades\Log;

class SupplierHealthMonitor
{
    private int $consecutiveFailureThreshold = 3;

    public function __construct(
        private readonly SupplierManager $manager,
        private readonly SupplierApiLogger $logger,
    ) {}

    /**
     * Run a health check against a supplier and update its status.
     */
    public function check(SupplierApi $supplier): HealthResult
    {
        $logId = $this->logger->logRequest(
            supplierApiId: $supplier->id,
            action: 'health_check',
            endpoint: $supplier->base_url,
            method: 'GET',
        );

        try {
            $driver = $this->manager->driver($supplier);
            $result = $driver->healthCheck();

            $this->logger->logResponse(
                logId: $logId,
                httpStatusCode: $result->isHealthy() ? 200 : 503,
                responsePayload: ['status' => $result->status, 'message' => $result->message],
                responseTimeMs: $result->latencyMs,
                status: $result->isHealthy() ? 'success' : 'failed',
            );

            $this->updateHealthStatus($supplier, $result);

            return $result;
        } catch (\Throwable $e) {
            $this->logger->logError($logId, $e->getMessage());
            $result = new HealthResult(status: 'down', message: $e->getMessage());
            $this->updateHealthStatus($supplier, $result);

            return $result;
        }
    }

    /**
     * Run health checks on all active suppliers.
     */
    public function checkAll(): void
    {
        $suppliers = SupplierApi::active()->get();

        foreach ($suppliers as $supplier) {
            try {
                $this->check($supplier);
            } catch (\Throwable $e) {
                Log::error('SupplierHealthMonitor: check failed', [
                    'supplier_id' => $supplier->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Mark a supplier as healthy.
     */
    public function markHealthy(int $supplierApiId): void
    {
        SupplierApi::where('id', $supplierApiId)->update([
            'health_status' => 'healthy',
            'health_checked_at' => now(),
        ]);
    }

    /**
     * Mark a supplier as degraded.
     */
    public function markDegraded(int $supplierApiId): void
    {
        SupplierApi::where('id', $supplierApiId)->update([
            'health_status' => 'degraded',
            'health_checked_at' => now(),
        ]);
    }

    /**
     * Mark a supplier as down.
     */
    public function markDown(int $supplierApiId): void
    {
        SupplierApi::where('id', $supplierApiId)->update([
            'health_status' => 'down',
            'health_checked_at' => now(),
        ]);
    }

    /**
     * Update supplier health status and auto-disable after consecutive failures.
     */
    private function updateHealthStatus(SupplierApi $supplier, HealthResult $result): void
    {
        $supplier->update([
            'health_status' => $result->status,
            'health_checked_at' => now(),
        ]);

        if ($result->isDown()) {
            $this->handleConsecutiveFailure($supplier);
        } else {
            $this->resetFailureCounter($supplier->id);
        }
    }

    /**
     * Track consecutive failures and auto-disable supplier after threshold.
     */
    private function handleConsecutiveFailure(SupplierApi $supplier): void
    {
        $cacheKey = "supplier_health_failures:{$supplier->id}";
        $failures = (int) cache($cacheKey, 0) + 1;
        cache([$cacheKey => $failures], now()->addHours(1));

        if ($failures >= $this->consecutiveFailureThreshold && $supplier->is_active) {
            $supplier->update(['is_active' => false]);
            Log::warning('SupplierHealthMonitor: auto-disabled supplier after consecutive failures', [
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->name,
                'failures' => $failures,
            ]);
        }
    }

    private function resetFailureCounter(int $supplierApiId): void
    {
        cache()->forget("supplier_health_failures:{$supplierApiId}");
    }
}
