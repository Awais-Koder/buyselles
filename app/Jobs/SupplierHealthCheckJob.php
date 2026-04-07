<?php

namespace App\Jobs;

use App\Services\Supplier\SupplierHealthMonitor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Periodic health check job — runs every 5 minutes via scheduler.
 *
 * Pings each active supplier's health endpoint.
 * Auto-disables suppliers after 3 consecutive failures.
 */
class SupplierHealthCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 120;

    public function __construct() {}

    public function handle(SupplierHealthMonitor $monitor): void
    {
        try {
            $monitor->checkAll();
            Log::info('SupplierHealthCheckJob: completed');
        } catch (\Throwable $e) {
            Log::error('SupplierHealthCheckJob: failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
