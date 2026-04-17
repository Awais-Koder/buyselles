<?php

namespace App\Services;

use App\Enums\DisputePriority;
use App\Enums\DisputeStatus;
use App\Enums\DisputeUserType;
use App\Enums\EscrowStatus;
use App\Models\Dispute;
use App\Models\DisputeMessage;
use App\Models\DisputeReason;
use App\Models\DisputeStatusLog;
use App\Models\Escrow;
use App\Models\Order;
use App\Utils\Helpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DisputeService
{
    public function __construct(
        protected EscrowService $escrowService,
    ) {}

    /**
     * Check if a dispute can be opened for the given order.
     */
    public function canOpenDispute(Order $order, string $userType): array
    {
        $escrowEnabled = (int) (getWebConfig(name: 'escrow_protection_status') ?? 0);
        if (! $escrowEnabled) {
            return ['allowed' => false, 'reason' => translate('dispute_system_is_currently_disabled_by_the_administrator')];
        }

        // Check if escrow is enabled for the order's product type(s)
        $escrowPhysical = (int) (getWebConfig(name: 'escrow_physical_products') ?? 0);
        $escrowDigital = (int) (getWebConfig(name: 'escrow_digital_products') ?? 0);
        $orderDetails = $order->relationLoaded('details') ? $order->details : $order->details()->get();

        $hasPhysical = false;
        $hasDigital = false;
        foreach ($orderDetails as $detail) {
            $productDetails = is_string($detail->product_details) ? json_decode($detail->product_details, true) : $detail->product_details;
            $productType = $productDetails['product_type'] ?? 'physical';
            if ($productType === 'digital') {
                $hasDigital = true;
            } else {
                $hasPhysical = true;
            }
        }

        if ($hasDigital && ! $escrowDigital && $hasPhysical && ! $escrowPhysical) {
            return ['allowed' => false, 'reason' => translate('escrow_protection_is_not_enabled_for_the_products_in_this_order')];
        }
        if ($hasDigital && ! $hasPhysical && ! $escrowDigital) {
            return ['allowed' => false, 'reason' => translate('escrow_protection_is_not_enabled_for_digital_products')];
        }
        if ($hasPhysical && ! $hasDigital && ! $escrowPhysical) {
            return ['allowed' => false, 'reason' => translate('escrow_protection_is_not_enabled_for_physical_products')];
        }

        if (! in_array($order->order_status, ['delivered', 'completed'])) {
            return ['allowed' => false, 'reason' => translate('dispute_can_only_be_opened_for_delivered_orders')];
        }

        if ($order->dispute_status === 'active') {
            return ['allowed' => false, 'reason' => translate('an_active_dispute_already_exists_for_this_order')];
        }

        $disputeWindowDays = (int) (getWebConfig(name: 'dispute_window_days') ?? 7);
        $deliveredAt = $order->updated_at;
        $daysSinceDelivery = $deliveredAt ? (int) abs(now()->diffInDays($deliveredAt)) : 0;
        if ($deliveredAt && $daysSinceDelivery > $disputeWindowDays) {
            return ['allowed' => false, 'reason' => translate('dispute_window_has_expired') . '. ' . translate('disputes_must_be_opened_within') . ' ' . $disputeWindowDays . ' ' . translate('days_of_delivery')];
        }

        // COD/offline orders — no escrow, so disputes go through standard refund flow
        if (in_array($order->payment_method, ['cash_on_delivery', 'offline_payment'])) {
            return ['allowed' => false, 'reason' => translate('disputes_are_not_available_for_cod_orders')];
        }

        return ['allowed' => true, 'reason' => null];
    }

    /**
     * Create a new dispute for an order.
     */
    public function createDispute(array $data, string $initiatedBy): Dispute
    {
        return DB::transaction(function () use ($data, $initiatedBy) {
            $order = Order::findOrFail($data['order_id']);

            $reason = isset($data['reason_id']) ? DisputeReason::find($data['reason_id']) : null;
            $vendorDeadlineHours = (int) (getWebConfig(name: 'dispute_vendor_response_hours') ?? 72);

            $dispute = Dispute::create([
                'order_id' => $order->id,
                'order_detail_id' => $data['order_detail_id'] ?? null,
                'buyer_id' => $order->customer_id,
                'vendor_id' => $order->seller_id,
                'initiated_by' => $initiatedBy,
                'reason_id' => $data['reason_id'] ?? null,
                'description' => $data['description'],
                'status' => DisputeStatus::OPEN,
                'priority' => $reason?->priority_default ?? DisputePriority::MEDIUM,
                'vendor_deadline_at' => $initiatedBy === DisputeUserType::BUYER
                    ? now()->addHours($vendorDeadlineHours)
                    : null,
            ]);

            DisputeStatusLog::create([
                'dispute_id' => $dispute->id,
                'changed_by' => $data['user_id'],
                'changed_by_type' => $initiatedBy,
                'from_status' => null,
                'to_status' => DisputeStatus::OPEN,
                'note' => 'Dispute opened',
                'created_at' => now(),
            ]);

            DisputeMessage::create([
                'dispute_id' => $dispute->id,
                'sender_id' => $data['user_id'],
                'sender_type' => $initiatedBy,
                'message' => $data['description'],
                'created_at' => now(),
            ]);

            // Freeze escrow if exists
            $escrow = Escrow::where('order_id', $order->id)
                ->where('status', EscrowStatus::HELD)
                ->first();

            if ($escrow) {
                $this->escrowService->freezeEscrow($escrow, $dispute);
            }

            $order->update(['dispute_status' => 'active']);

            // Notify vendor
            $seller = $dispute->vendor ?? null;
            if ($seller && $seller->cm_firebase_token) {
                Helpers::send_push_notif_to_device($seller->cm_firebase_token, [
                    'title' => translate('New Dispute Opened'),
                    'description' => translate('A buyer has opened dispute #') . $dispute->id . translate(' on order #') . $order->id,
                    'order_id' => $order->id,
                    'type' => 'dispute',
                ]);
            }

            return $dispute;
        });
    }

    /**
     * Record a vendor response to a dispute.
     */
    public function vendorRespond(Dispute $dispute, int $vendorUserId, string $message): Dispute
    {
        return DB::transaction(function () use ($dispute, $vendorUserId, $message) {
            $oldStatus = $dispute->status;

            if ($dispute->status === DisputeStatus::OPEN) {
                $dispute->update(['status' => DisputeStatus::VENDOR_RESPONSE]);

                DisputeStatusLog::create([
                    'dispute_id' => $dispute->id,
                    'changed_by' => $vendorUserId,
                    'changed_by_type' => DisputeUserType::VENDOR,
                    'from_status' => $oldStatus,
                    'to_status' => DisputeStatus::VENDOR_RESPONSE,
                    'note' => 'Vendor responded',
                    'created_at' => now(),
                ]);
            }

            DisputeMessage::create([
                'dispute_id' => $dispute->id,
                'sender_id' => $vendorUserId,
                'sender_type' => DisputeUserType::VENDOR,
                'message' => $message,
                'created_at' => now(),
            ]);

            // Notify buyer
            $buyer = $dispute->buyer ?? null;
            if ($buyer && $buyer->cm_firebase_token) {
                Helpers::send_push_notif_to_device($buyer->cm_firebase_token, [
                    'title' => translate('Vendor Responded to Dispute'),
                    'description' => translate('The vendor has responded to your dispute #') . $dispute->id,
                    'order_id' => $dispute->order_id,
                    'type' => 'dispute',
                ]);
            }

            return $dispute->fresh();
        });
    }

    /**
     * Escalate a dispute to admin review.
     */
    public function escalateToAdmin(Dispute $dispute, int $userId, string $userType): Dispute
    {
        return DB::transaction(function () use ($dispute, $userId, $userType) {
            $oldStatus = $dispute->status;

            $dispute->update([
                'status' => DisputeStatus::UNDER_REVIEW,
                'escalated_at' => now(),
            ]);

            DisputeStatusLog::create([
                'dispute_id' => $dispute->id,
                'changed_by' => $userId,
                'changed_by_type' => $userType,
                'from_status' => $oldStatus,
                'to_status' => DisputeStatus::UNDER_REVIEW,
                'note' => 'Escalated to admin review',
                'created_at' => now(),
            ]);

            return $dispute->fresh();
        });
    }

    /**
     * Resolve dispute with full refund to buyer.
     */
    public function resolveRefund(Dispute $dispute, int $adminId, string $decision, ?string $adminNote = null): Dispute
    {
        return DB::transaction(function () use ($dispute, $adminId, $decision, $adminNote) {
            $oldStatus = $dispute->status;

            $dispute->update([
                'status' => DisputeStatus::RESOLVED_REFUND,
                'admin_decision' => $decision,
                'admin_note' => $adminNote,
                'resolved_by' => $adminId,
                'resolved_at' => now(),
            ]);

            DisputeStatusLog::create([
                'dispute_id' => $dispute->id,
                'changed_by' => $adminId,
                'changed_by_type' => DisputeUserType::ADMIN,
                'from_status' => $oldStatus,
                'to_status' => DisputeStatus::RESOLVED_REFUND,
                'note' => $decision,
                'created_at' => now(),
            ]);

            // Refund the escrow
            $escrow = Escrow::where('order_id', $dispute->order_id)
                ->where('status', EscrowStatus::DISPUTED)
                ->first();

            if ($escrow) {
                $this->escrowService->refundEscrow($escrow, $adminId);
            }

            $dispute->order->update(['dispute_status' => 'resolved']);

            // Notify buyer
            $buyer = $dispute->buyer ?? null;
            if ($buyer && $buyer->cm_firebase_token) {
                Helpers::send_push_notif_to_device($buyer->cm_firebase_token, [
                    'title' => translate('Dispute Resolved – Refund Approved'),
                    'description' => translate('Admin resolved dispute #') . $dispute->id . translate(' in your favour. Refund is being processed.'),
                    'order_id' => $dispute->order_id,
                    'type' => 'dispute',
                ]);
            }

            // Notify vendor
            $vendor = $dispute->vendor ?? null;
            if ($vendor && $vendor->cm_firebase_token) {
                Helpers::send_push_notif_to_device($vendor->cm_firebase_token, [
                    'title' => translate('Dispute Resolved'),
                    'description' => translate('Admin resolved dispute #') . $dispute->id . translate(' – refund issued to buyer.'),
                    'order_id' => $dispute->order_id,
                    'type' => 'dispute',
                ]);
            }

            return $dispute->fresh();
        });
    }

    /**
     * Resolve dispute in vendor's favor — release escrow.
     */
    public function resolveRelease(Dispute $dispute, int $adminId, string $decision, ?string $adminNote = null): Dispute
    {
        return DB::transaction(function () use ($dispute, $adminId, $decision, $adminNote) {
            $oldStatus = $dispute->status;

            $dispute->update([
                'status' => DisputeStatus::RESOLVED_RELEASE,
                'admin_decision' => $decision,
                'admin_note' => $adminNote,
                'resolved_by' => $adminId,
                'resolved_at' => now(),
            ]);

            DisputeStatusLog::create([
                'dispute_id' => $dispute->id,
                'changed_by' => $adminId,
                'changed_by_type' => DisputeUserType::ADMIN,
                'from_status' => $oldStatus,
                'to_status' => DisputeStatus::RESOLVED_RELEASE,
                'note' => $decision,
                'created_at' => now(),
            ]);

            // Unfreeze and release escrow
            $escrow = Escrow::where('order_id', $dispute->order_id)
                ->where('status', EscrowStatus::DISPUTED)
                ->first();

            if ($escrow) {
                $this->escrowService->unfreezeEscrow($escrow);
                $this->escrowService->releaseEscrow($escrow, 'admin');
            }

            $dispute->order->update(['dispute_status' => 'resolved']);

            // Notify vendor
            $vendor = $dispute->vendor ?? null;
            if ($vendor && $vendor->cm_firebase_token) {
                Helpers::send_push_notif_to_device($vendor->cm_firebase_token, [
                    'title' => translate('Dispute Resolved – Funds Released'),
                    'description' => translate('Admin resolved dispute #') . $dispute->id . translate(' in your favour. Escrow funds have been released.'),
                    'order_id' => $dispute->order_id,
                    'type' => 'dispute',
                ]);
            }

            // Notify buyer
            $buyer = $dispute->buyer ?? null;
            if ($buyer && $buyer->cm_firebase_token) {
                Helpers::send_push_notif_to_device($buyer->cm_firebase_token, [
                    'title' => translate('Dispute Resolved'),
                    'description' => translate('Admin resolved dispute #') . $dispute->id . translate(' – payment released to vendor.'),
                    'order_id' => $dispute->order_id,
                    'type' => 'dispute',
                ]);
            }

            return $dispute->fresh();
        });
    }

    /**
     * Add a message to a dispute conversation.
     */
    public function addMessage(Dispute $dispute, int $senderId, string $senderType, string $message): DisputeMessage
    {
        return DisputeMessage::create([
            'dispute_id' => $dispute->id,
            'sender_id' => $senderId,
            'sender_type' => $senderType,
            'message' => $message,
            'created_at' => now(),
        ]);
    }

    /**
     * Close a dispute (admin action).
     */
    public function closeDispute(Dispute $dispute, int $adminId, ?string $note = null): Dispute
    {
        return DB::transaction(function () use ($dispute, $adminId, $note) {
            $oldStatus = $dispute->status;

            $dispute->update([
                'status' => DisputeStatus::CLOSED,
                'resolved_by' => $adminId,
                'resolved_at' => now(),
                'admin_note' => $note,
            ]);

            DisputeStatusLog::create([
                'dispute_id' => $dispute->id,
                'changed_by' => $adminId,
                'changed_by_type' => DisputeUserType::ADMIN,
                'from_status' => $oldStatus,
                'to_status' => DisputeStatus::CLOSED,
                'note' => $note ?? 'Dispute closed by admin',
                'created_at' => now(),
            ]);

            // If escrow was frozen, release it back to vendor
            $escrow = Escrow::where('order_id', $dispute->order_id)
                ->where('status', EscrowStatus::DISPUTED)
                ->first();

            if ($escrow) {
                $this->escrowService->unfreezeEscrow($escrow);
                $this->escrowService->releaseEscrow($escrow, 'admin');
            }

            $dispute->order->update(['dispute_status' => 'resolved']);

            return $dispute->fresh();
        });
    }

    /**
     * Auto-close inactive disputes (system job).
     */
    public function autoCloseInactive(int $inactiveDays = 14): int
    {
        $cutoff = now()->subDays($inactiveDays);
        $disputes = Dispute::whereIn('status', [DisputeStatus::OPEN, DisputeStatus::VENDOR_RESPONSE])
            ->where('updated_at', '<', $cutoff)
            ->get();

        $count = 0;
        foreach ($disputes as $dispute) {
            try {
                DB::transaction(function () use ($dispute) {
                    $oldStatus = $dispute->status;

                    $dispute->update([
                        'status' => DisputeStatus::AUTO_CLOSED,
                        'resolved_at' => now(),
                    ]);

                    DisputeStatusLog::create([
                        'dispute_id' => $dispute->id,
                        'changed_by' => null,
                        'changed_by_type' => DisputeUserType::SYSTEM,
                        'from_status' => $oldStatus,
                        'to_status' => DisputeStatus::AUTO_CLOSED,
                        'note' => 'Auto-closed due to inactivity',
                        'created_at' => now(),
                    ]);

                    // Release escrow back to vendor
                    $escrow = Escrow::where('order_id', $dispute->order_id)
                        ->where('status', EscrowStatus::DISPUTED)
                        ->first();

                    if ($escrow) {
                        $this->escrowService->unfreezeEscrow($escrow);
                        $this->escrowService->releaseEscrow($escrow, 'auto');
                    }

                    $dispute->order->update(['dispute_status' => 'resolved']);
                });
                $count++;
            } catch (\Throwable $e) {
                Log::error('DisputeService::autoCloseInactive failed', [
                    'dispute_id' => $dispute->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    /**
     * Auto-escalate disputes where vendor deadline has passed.
     */
    public function autoEscalateOverdue(): int
    {
        $disputes = Dispute::where('status', DisputeStatus::OPEN)
            ->whereNotNull('vendor_deadline_at')
            ->where('vendor_deadline_at', '<=', now())
            ->get();

        $count = 0;
        foreach ($disputes as $dispute) {
            try {
                $this->escalateToAdmin($dispute, 0, DisputeUserType::SYSTEM);
                $count++;
            } catch (\Throwable $e) {
                Log::error('DisputeService::autoEscalateOverdue failed', [
                    'dispute_id' => $dispute->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    /**
     * Get allowed status transitions.
     */
    public function getAllowedTransitions(string $currentStatus): array
    {
        return match ($currentStatus) {
            DisputeStatus::OPEN => [DisputeStatus::VENDOR_RESPONSE, DisputeStatus::UNDER_REVIEW],
            DisputeStatus::VENDOR_RESPONSE => [DisputeStatus::UNDER_REVIEW],
            DisputeStatus::UNDER_REVIEW => [DisputeStatus::RESOLVED_REFUND, DisputeStatus::RESOLVED_RELEASE, DisputeStatus::CLOSED],
            default => [],
        };
    }
}
