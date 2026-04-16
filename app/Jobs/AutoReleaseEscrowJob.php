<?php

namespace App\Jobs;

use App\Enums\EscrowStatus;
use App\Models\Escrow;
use App\Services\EscrowService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoReleaseEscrowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct() {}

    public function handle(EscrowService $escrowService): void
    {
        $escrows = Escrow::where('status', EscrowStatus::HELD)
            ->whereNotNull('auto_release_at')
            ->where('auto_release_at', '<=', now())
            ->whereNull('dispute_id')
            ->get();

        foreach ($escrows as $escrow) {
            try {
                $escrowService->releaseEscrow($escrow, 'auto');
            } catch (\Throwable $e) {
                Log::error('AutoReleaseEscrowJob: failed to release escrow', [
                    'escrow_id' => $escrow->id,
                    'order_id' => $escrow->order_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
