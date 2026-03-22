<?php

use App\Console\Commands\MarkExpiredDigitalCodesCommand;
use Illuminate\Support\Facades\Schedule;

// Mark digital product codes that have passed their expiry date.
// Runs nightly at 03:00 server time.
Schedule::command(MarkExpiredDigitalCodesCommand::class)->dailyAt('03:00');
