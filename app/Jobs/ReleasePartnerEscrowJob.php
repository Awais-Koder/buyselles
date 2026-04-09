<?php

namespace App\Jobs;

use App\Models\SellerWallet;
use App\Models\SellerWalletHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReleasePartnerEscrowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public readonly int $orderId,
        public readonly int $sellerId,
        public readonly float $amount,
    ) {}

    public function handle(): void
    {
        try {
            DB::transaction(function () {
                $wallet = SellerWallet::where('seller_id', $this->sellerId)
                    ->lockForUpdate()
                    ->first();

                if (! $wallet) {
                    Log::warning('ReleasePartnerEscrowJob: wallet not found', [
                        'seller_id' => $this->sellerId,
                        'order_id' => $this->orderId,
                    ]);

                    return;
                }

                // Guard: do not release more than is currently in pending_balance
                $releaseAmount = min($this->amount, (float) $wallet->pending_balance);

                if ($releaseAmount <= 0) {
                    return;
                }

                $wallet->pending_balance = max(0, $wallet->pending_balance - $releaseAmount);
                $wallet->total_earning = $wallet->total_earning + $releaseAmount;
                $wallet->save();

                // Record the movement in wallet history
                SellerWalletHistory::create([
                    'seller_id' => $this->sellerId,
                    'amount' => $releaseAmount,
                    'order_id' => $this->orderId,
                    'product_id' => null,
                    'payment' => 'partner_escrow_release',
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('ReleasePartnerEscrowJob failed', [
                'order_id' => $this->orderId,
                'seller_id' => $this->sellerId,
                'amount' => $this->amount,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
