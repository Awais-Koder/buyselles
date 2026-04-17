<?php

namespace App\Services;

use App\Enums\EscrowStatus;
use App\Models\AdminWallet;
use App\Models\AdminWalletHistory;
use App\Models\Dispute;
use App\Models\Escrow;
use App\Models\Order;
use App\Models\SellerWallet;
use App\Models\SellerWalletHistory;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EscrowService
{
    /**
     * Check if escrow protection is enabled globally.
     */
    public function isEscrowEnabled(): bool
    {
        return (bool) (getWebConfig(name: 'escrow_protection_status') ?? 0);
    }

    /**
     * Check if an order qualifies for escrow protection.
     * Escrow applies only to third-party vendor orders with online/wallet payment.
     */
    public function isEscrowEligible(Order $order): bool
    {
        if (! $this->isEscrowEnabled()) {
            return false;
        }

        if ($order->seller_is === 'admin') {
            return false;
        }

        if (in_array($order->payment_method, ['cash_on_delivery', 'offline_payment'])) {
            return false;
        }

        $physicalEnabled = (bool) (getWebConfig(name: 'escrow_physical_products') ?? 0);
        $digitalEnabled = (bool) (getWebConfig(name: 'escrow_digital_products') ?? 0);

        if (! $physicalEnabled && ! $digitalEnabled) {
            return false;
        }

        $details = $order->relationLoaded('orderDetails')
            ? $order->orderDetails
            : $order->orderDetails()->get();

        $hasPhysical = false;
        $hasDigital = false;

        foreach ($details as $detail) {
            $productData = is_string($detail->product_details)
                ? json_decode($detail->product_details, true)
                : (array) $detail->product_details;

            $productType = $productData['product_type'] ?? 'physical';

            if ($productType === 'digital') {
                $hasDigital = true;
            } else {
                $hasPhysical = true;
            }
        }

        if ($hasPhysical && $physicalEnabled) {
            return true;
        }

        if ($hasDigital && $digitalEnabled) {
            return true;
        }

        return false;
    }

    /**
     * Create an escrow hold for a delivered order.
     * The seller's earning goes to pending_balance instead of total_earning.
     * Called from OrderManager when escrow is applicable.
     */
    public function createEscrow(Order $order, float $sellerEarning, float $commission, float $serviceFee): Escrow
    {
        $autoReleaseHours = (int) (getWebConfig(name: 'escrow_auto_release_hours') ?? 48);

        return Escrow::create([
            'order_id' => $order->id,
            'seller_id' => $order->seller_id,
            'buyer_id' => $order->customer_id,
            'amount' => $sellerEarning + $commission + $serviceFee,
            'admin_commission' => $commission,
            'seller_amount' => $sellerEarning,
            'service_fee' => $serviceFee,
            'status' => EscrowStatus::HELD,
            'payment_method' => $order->payment_method,
            'auto_release_at' => now()->addHours($autoReleaseHours),
        ]);
    }

    /**
     * Release escrow funds to the vendor.
     * Moves seller_amount from pending_balance → total_earning.
     */
    public function releaseEscrow(Escrow $escrow, string $releasedBy = 'auto'): void
    {
        DB::transaction(function () use ($escrow, $releasedBy) {
            $wallet = SellerWallet::where('seller_id', $escrow->seller_id)
                ->lockForUpdate()
                ->first();

            if (! $wallet) {
                Log::warning('EscrowService::releaseEscrow: wallet not found', [
                    'escrow_id' => $escrow->id,
                    'seller_id' => $escrow->seller_id,
                ]);

                return;
            }

            $releaseAmount = min($escrow->seller_amount, (float) $wallet->pending_balance);

            if ($releaseAmount <= 0) {
                Log::warning('EscrowService::releaseEscrow: nothing to release', [
                    'escrow_id' => $escrow->id,
                    'seller_amount' => $escrow->seller_amount,
                    'pending_balance' => $wallet->pending_balance,
                ]);

                return;
            }

            $wallet->pending_balance = max(0, $wallet->pending_balance - $releaseAmount);
            $wallet->total_earning += $releaseAmount;
            $wallet->save();

            SellerWalletHistory::create([
                'seller_id' => $escrow->seller_id,
                'amount' => $releaseAmount,
                'order_id' => $escrow->order_id,
                'product_id' => null,
                'payment' => 'escrow_release',
            ]);

            $escrow->update([
                'status' => EscrowStatus::RELEASED,
                'released_at' => now(),
                'released_by' => $releasedBy,
            ]);
        });
    }

    /**
     * Refund escrow to the buyer (dispute resolved in buyer's favor).
     * Debits seller pending_balance, reverses admin commission, credits buyer wallet.
     */
    public function refundEscrow(Escrow $escrow, ?int $adminId = null): void
    {
        DB::transaction(function () use ($escrow, $adminId) {
            // 1. Debit seller's pending_balance
            $sellerWallet = SellerWallet::where('seller_id', $escrow->seller_id)
                ->lockForUpdate()
                ->first();

            if ($sellerWallet) {
                $debitAmount = min($escrow->seller_amount, (float) $sellerWallet->pending_balance);
                $sellerWallet->pending_balance = max(0, $sellerWallet->pending_balance - $debitAmount);
                $sellerWallet->commission_given = max(0, $sellerWallet->commission_given - $escrow->admin_commission);
                $sellerWallet->save();

                SellerWalletHistory::create([
                    'seller_id' => $escrow->seller_id,
                    'amount' => -$debitAmount,
                    'order_id' => $escrow->order_id,
                    'product_id' => null,
                    'payment' => 'escrow_refund',
                ]);
            }

            // 2. Reverse admin commission + service fee
            $adminWallet = AdminWallet::where('admin_id', 1)
                ->lockForUpdate()
                ->first();

            if ($adminWallet) {
                $adminReversal = $escrow->admin_commission + $escrow->service_fee;
                $adminWallet->commission_earned = max(0, $adminWallet->commission_earned - $adminReversal);
                $adminWallet->save();

                AdminWalletHistory::create([
                    'admin_id' => 1,
                    'amount' => -$adminReversal,
                    'order_id' => $escrow->order_id,
                    'product_id' => null,
                    'payment' => 'escrow_refund',
                ]);
            }

            // 3. Credit buyer's wallet (bypass wallet_status check — dispute refunds always work)
            $refundAmount = $escrow->seller_amount + $escrow->admin_commission + $escrow->service_fee;
            $customer = User::where('id', $escrow->buyer_id)
                ->lockForUpdate()
                ->first();

            if ($customer) {
                $currentBalance = (float) $customer->wallet_balance;
                $customer->wallet_balance = $currentBalance + $refundAmount;
                $customer->save();

                $walletTransaction = new WalletTransaction;
                $walletTransaction->user_id = $customer->id;
                $walletTransaction->transaction_id = Str::uuid();
                $walletTransaction->reference = 'dispute_refund';
                $walletTransaction->transaction_type = 'order_refund';
                $walletTransaction->credit = $refundAmount;
                $walletTransaction->debit = 0;
                $walletTransaction->balance = $customer->wallet_balance;
                $walletTransaction->order_ids = [$escrow->order_id];
                $walletTransaction->created_at = now();
                $walletTransaction->updated_at = now();
                $walletTransaction->save();
            }

            // 4. Update escrow status
            $escrow->update([
                'status' => EscrowStatus::REFUNDED,
                'released_at' => now(),
                'released_by' => $adminId ? 'admin' : 'system',
            ]);
        });
    }

    /**
     * Freeze escrow when a dispute is opened.
     * Stops auto-release timer and links the dispute.
     */
    public function freezeEscrow(Escrow $escrow, Dispute $dispute): void
    {
        $escrow->update([
            'status' => EscrowStatus::DISPUTED,
            'auto_release_at' => null,
            'dispute_id' => $dispute->id,
        ]);
    }

    /**
     * Unfreeze escrow when a dispute is resolved in vendor's favor.
     * Restores auto-release timer and unlinks the dispute.
     */
    public function unfreezeEscrow(Escrow $escrow): void
    {
        $autoReleaseHours = (int) (getWebConfig(name: 'escrow_auto_release_hours') ?? 48);

        $escrow->update([
            'status' => EscrowStatus::HELD,
            'auto_release_at' => now()->addHours($autoReleaseHours),
            'dispute_id' => null,
        ]);
    }
}
