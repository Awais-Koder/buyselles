<?php

namespace App\Console\Commands;

use App\Services\DigitalProductCodeService;
use Illuminate\Console\Command;

class MarkExpiredDigitalCodesCommand extends Command
{
    protected $signature = 'digital:mark-expired';

    protected $description = 'Mark digital product codes that have passed their expiry date as expired, and sync stock counts.';

    public function handle(DigitalProductCodeService $service): int
    {
        $count = $service->markExpiredCodes();

        $this->info("Marked {$count} digital product code(s) as expired.");

        return self::SUCCESS;
    }
}
