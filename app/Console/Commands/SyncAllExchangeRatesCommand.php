<?php

namespace App\Console\Commands;

use App\Services\ExchangeRateService;
use Illuminate\Console\Command;

class SyncAllExchangeRatesCommand extends Command
{
    protected $signature = 'currency:sync-all-rates';

    protected $description = 'Fetch latest exchange rates and update ALL currencies in the database';

    public function handle(ExchangeRateService $service): int
    {
        $this->info('Fetching latest exchange rates from API...');

        try {
            $result = $service->syncAll('api_manual');
            $this->info('Sync completed successfully.');
            $this->newLine();

            $this->table(
                ['Currency', 'Status', 'Old Rate', 'New Rate'],
                collect($result['results'])->map(function ($r, $code) {
                    return [
                        $code,
                        $r['status'],
                        $r['old_rate'] ?? '-',
                        $r['new_rate'] ?? $r['rate'] ?? '-',
                    ];
                })->toArray(),
            );

            $updated = collect($result['results'])->where('status', 'updated')->count();
            $unchanged = collect($result['results'])->where('status', 'unchanged')->count();
            $skipped = collect($result['results'])->where('status', 'skipped')->count();

            $this->newLine();
            $this->info("Summary: {$updated} updated, {$unchanged} unchanged, {$skipped} skipped.");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Sync failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
