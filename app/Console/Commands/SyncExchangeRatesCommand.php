<?php

namespace App\Console\Commands;

use App\Jobs\SyncExchangeRatesJob;
use Illuminate\Console\Command;

class SyncExchangeRatesCommand extends Command
{
    protected $signature = 'currency:sync-rates';

    protected $description = 'Fetch latest exchange rates from the configured API and update JOD, SAR, AED currencies';

    public function handle(): int
    {
        $this->info('Dispatching exchange rate sync job...');

        SyncExchangeRatesJob::dispatch(source: 'api_cron');

        $this->info('Sync job dispatched successfully.');

        return self::SUCCESS;
    }
}
