<?php

namespace App\Jobs;

use App\Models\SupplierApi;
use App\Services\Supplier\SupplierManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Fetches the full supplier product catalog in the background.
 *
 * Progress is tracked in cache so the admin UI can poll for status.
 * Once complete, the catalog is stored in cache for instant browsing.
 */
class SyncSupplierCatalogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 900; // 15 minutes — large catalogs need time

    public function __construct(
        public readonly int $supplierId,
    ) {}

    public function handle(SupplierManager $manager): void
    {
        $supplier = SupplierApi::find($this->supplierId);

        if (! $supplier) {
            return;
        }

        $statusKey = self::statusCacheKey($this->supplierId);
        $catalogKey = self::catalogCacheKey($this->supplierId);

        try {
            Cache::put($statusKey, [
                'state' => 'running',
                'progress' => 0,
                'total' => null,
                'fetched' => 0,
                'started_at' => now()->toIso8601String(),
            ], now()->addMinutes(30));

            $driver = $manager->driver($supplier);

            // Fetch all products with progress tracking via callback
            $allProducts = [];
            $totalBrands = null;
            $pagesFetched = 0;

            // Use fetch_all with a progress callback embedded in the driver
            $products = $driver->fetchProducts([
                'fetch_all' => true,
                'size' => 100,
                'on_page' => function (int $page, int $brandsOnPage, int $totalBrandCount) use ($statusKey, &$pagesFetched, &$totalBrands) {
                    $pagesFetched = $page + 1;
                    $totalBrands = $totalBrandCount;
                    $totalPages = $totalBrandCount > 0 ? (int) ceil($totalBrandCount / 100) : 0;
                    $progress = $totalPages > 0 ? min(99, (int) round(($pagesFetched / $totalPages) * 100)) : 0;

                    Cache::put($statusKey, [
                        'state' => 'running',
                        'progress' => $progress,
                        'total_brands' => $totalBrandCount,
                        'pages_fetched' => $pagesFetched,
                        'total_pages' => $totalPages,
                        'started_at' => Cache::get($statusKey)['started_at'] ?? now()->toIso8601String(),
                    ], now()->addMinutes(30));
                },
            ]);

            $catalog = collect($products)->map(fn ($p) => [
                'id' => $p->supplierProductId,
                'name' => $p->name,
                'price' => $p->price,
                'currency' => $p->currency,
                'stock' => $p->stockAvailable,
                'region' => $p->region,
                'image' => $p->imageUrl,
            ])->values()->all();

            // Store catalog in cache for 6 hours
            Cache::put($catalogKey, $catalog, now()->addHours(6));

            Cache::put($statusKey, [
                'state' => 'done',
                'progress' => 100,
                'has_catalog' => true,
                'total_products' => count($catalog),
                'total_brands' => $totalBrands,
                'pages_fetched' => $pagesFetched,
                'finished_at' => now()->toIso8601String(),
            ], now()->addMinutes(30));

            Log::info('SyncSupplierCatalogJob: completed', [
                'supplier_id' => $this->supplierId,
                'products' => count($catalog),
            ]);
        } catch (\Throwable $e) {
            Cache::put($statusKey, [
                'state' => 'failed',
                'error' => $e->getMessage(),
                'failed_at' => now()->toIso8601String(),
            ], now()->addMinutes(30));

            Log::error('SyncSupplierCatalogJob: failed', [
                'supplier_id' => $this->supplierId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public static function statusCacheKey(int $supplierId): string
    {
        return "supplier_catalog_sync_status_{$supplierId}";
    }

    public static function catalogCacheKey(int $supplierId): string
    {
        return "supplier_catalog_{$supplierId}";
    }
}
