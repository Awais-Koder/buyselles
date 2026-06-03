<?php

namespace App\Console\Commands;

use App\Models\BusinessSetting;
use App\Models\Currency;
use Illuminate\Console\Command;

class SetupBaseCurrencyCommand extends Command
{
    protected $signature = 'currency:setup-base';

    protected $description = 'Set USD as the absolute base currency with exchange_rate=1, switch to multi-currency mode, and add JOD with proper rate';

    public function handle(): int
    {
        $usdExchangeRate = 1.0;
        $jodExchangeRate = 0.709;
        $usd = Currency::query()->where('code', 'USD')->first();

        if (! $usd) {
            $usd = Currency::query()->create([
                'name' => 'USD',
                'symbol' => '$',
                'code' => 'USD',
                'exchange_rate' => $usdExchangeRate,
                'status' => true,
            ]);
            $this->info('Created USD currency.');
        } elseif (abs((float) $usd->exchange_rate - $usdExchangeRate) > 0.0001) {
            $usd->update(['exchange_rate' => $usdExchangeRate, 'status' => true]);
            $this->info('Updated USD exchange_rate to exactly 1.');
        }

        $jod = Currency::query()->where('code', 'JOD')->first();

        if (! $jod) {
            Currency::query()->create([
                'name' => 'Jordanian Dinar',
                'symbol' => 'د.ا',
                'code' => 'JOD',
                'exchange_rate' => $jodExchangeRate,
                'status' => true,
            ]);
            $this->info('Created JOD (Jordanian Dinar) with exchange_rate '.$jodExchangeRate.'.');
        } elseif (abs((float) $jod->exchange_rate - $jodExchangeRate) > 0.0001) {
            $jod->update(['exchange_rate' => $jodExchangeRate, 'status' => true]);
            $this->info('Updated JOD exchange_rate to '.$jodExchangeRate.'.');
        }

        BusinessSetting::query()->updateOrInsert(
            ['type' => 'system_default_currency'],
            ['value' => $usd->id],
        );

        BusinessSetting::query()->updateOrInsert(
            ['type' => 'currency_model'],
            ['value' => 'multi_currency'],
        );

        session()->forget('system_default_currency_info');
        session()->forget('currency_exchange_rate');
        session()->forget('currency_code');
        session()->forget('currency_symbol');
        session()->forget('usd');
        session()->forget('default');

        $this->info('Base currency set to USD (ID: '.$usd->id.'), currency model switched to multi_currency.');
        $this->info('Active currencies:');

        foreach (Currency::query()->where('status', true)->get() as $c) {
            $this->line("  {$c->code} ({$c->name}) — rate: {$c->exchange_rate}");
        }

        return self::SUCCESS;
    }
}
