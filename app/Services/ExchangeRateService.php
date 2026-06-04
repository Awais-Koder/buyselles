<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\ExchangeRateLog;
use Illuminate\Support\Facades\Http;

class ExchangeRateService
{
    public function sync(string $source = 'api_cron'): array
    {
        $url = config('currency.exchange_rate_api_url');
        $timeout = config('currency.exchange_rate_api_timeout', 30);
        $currenciesToSync = config('currency.sync_currencies', ['JOD', 'SAR', 'AED']);
        $results = [];

        try {
            $response = Http::timeout($timeout)->get($url);

            if (! $response->successful()) {
                throw new \RuntimeException('API returned '.$response->status().': '.$response->body());
            }

            $data = $response->json();
            $rates = $data['rates'] ?? [];

            foreach ($currenciesToSync as $code) {
                if (! isset($rates[$code])) {
                    $results[$code] = ['status' => 'skipped', 'reason' => 'Not in API response'];

                    continue;
                }

                $newRate = (float) $rates[$code];
                $currency = Currency::query()->where('code', $code)->first();

                if (! $currency) {
                    $results[$code] = ['status' => 'skipped', 'reason' => 'Currency not in DB'];

                    continue;
                }

                $oldRate = (float) $currency->exchange_rate;

                if (abs($oldRate - $newRate) < 0.0001) {
                    ExchangeRateLog::query()->create([
                        'currency_code' => $code,
                        'old_rate' => $oldRate,
                        'new_rate' => $newRate,
                        'source' => $source,
                        'api_response' => $data,
                        'status' => 'unchanged',
                        'created_at' => now(),
                    ]);

                    $results[$code] = ['status' => 'unchanged', 'rate' => $newRate];

                    continue;
                }

                $currency->update(['exchange_rate' => (string) $newRate]);

                ExchangeRateLog::query()->create([
                    'currency_code' => $code,
                    'old_rate' => $oldRate,
                    'new_rate' => $newRate,
                    'source' => $source,
                    'api_response' => $data,
                    'status' => 'success',
                    'created_at' => now(),
                ]);

                $results[$code] = ['status' => 'updated', 'old_rate' => $oldRate, 'new_rate' => $newRate];
            }

            $this->clearCurrencyCaches();

            return ['success' => true, 'results' => $results];

        } catch (\Throwable $e) {
            foreach ($currenciesToSync as $code) {
                $currency = Currency::query()->where('code', $code)->first();

                ExchangeRateLog::query()->create([
                    'currency_code' => $code,
                    'old_rate' => $currency ? (float) $currency->exchange_rate : 0,
                    'new_rate' => 0,
                    'source' => $source,
                    'status' => 'error',
                    'error_message' => $e->getMessage(),
                    'created_at' => now(),
                ]);
            }

            throw $e;
        }
    }

    public function syncAll(string $source = 'api_manual'): array
    {
        $url = config('currency.exchange_rate_api_url');
        $timeout = config('currency.exchange_rate_api_timeout', 30);
        $results = [];

        try {
            $response = Http::timeout($timeout)->get($url);

            if (! $response->successful()) {
                throw new \RuntimeException('API returned '.$response->status().': '.$response->body());
            }

            $data = $response->json();
            $rates = $data['rates'] ?? [];

            $currencies = Currency::query()->get();

            foreach ($currencies as $currency) {
                $code = $currency->code;

                if (! isset($rates[$code])) {
                    ExchangeRateLog::query()->create([
                        'currency_code' => $code,
                        'old_rate' => (float) $currency->exchange_rate,
                        'new_rate' => 0,
                        'source' => $source,
                        'api_response' => $data,
                        'status' => 'skipped',
                        'error_message' => 'Not in API response',
                        'created_at' => now(),
                    ]);

                    $results[$code] = ['status' => 'skipped', 'reason' => 'Not in API response'];

                    continue;
                }

                $newRate = (float) $rates[$code];
                $oldRate = (float) $currency->exchange_rate;

                if (abs($oldRate - $newRate) < 0.0001) {
                    ExchangeRateLog::query()->create([
                        'currency_code' => $code,
                        'old_rate' => $oldRate,
                        'new_rate' => $newRate,
                        'source' => $source,
                        'api_response' => $data,
                        'status' => 'unchanged',
                        'created_at' => now(),
                    ]);

                    $results[$code] = ['status' => 'unchanged', 'rate' => $newRate];

                    continue;
                }

                $currency->update(['exchange_rate' => (string) $newRate]);

                ExchangeRateLog::query()->create([
                    'currency_code' => $code,
                    'old_rate' => $oldRate,
                    'new_rate' => $newRate,
                    'source' => $source,
                    'api_response' => $data,
                    'status' => 'success',
                    'created_at' => now(),
                ]);

                $results[$code] = ['status' => 'updated', 'old_rate' => $oldRate, 'new_rate' => $newRate];
            }

            $this->clearCurrencyCaches();

            return ['success' => true, 'results' => $results];

        } catch (\Throwable $e) {
            $currencies = Currency::query()->get();

            foreach ($currencies as $currency) {
                ExchangeRateLog::query()->create([
                    'currency_code' => $currency->code,
                    'old_rate' => (float) $currency->exchange_rate,
                    'new_rate' => 0,
                    'source' => $source,
                    'status' => 'error',
                    'error_message' => $e->getMessage(),
                    'created_at' => now(),
                ]);
            }

            throw $e;
        }
    }

    private function clearCurrencyCaches(): void
    {
        session()->forget('usd');
        session()->forget('default');
        session()->forget('system_default_currency_info');
        session()->forget('currency_code');
        session()->forget('currency_symbol');
        session()->forget('currency_exchange_rate');
    }
}
