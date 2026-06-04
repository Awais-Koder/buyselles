<?php

namespace App\Jobs;

use App\Services\ExchangeRateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncExchangeRatesJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 60, 300];

    public function __construct(
        private readonly string $source = 'api_cron',
    ) {}

    public function uniqueId(): string
    {
        return 'sync-exchange-rates';
    }

    public function handle(ExchangeRateService $service): void
    {
        Log::info('Exchange rate sync started', ['source' => $this->source]);

        $result = $service->sync($this->source);

        Log::info('Exchange rate sync completed', ['source' => $this->source, 'results' => $result['results']]);
    }
}
