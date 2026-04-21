<?php

namespace App\Listeners;

use App\Enums\EmailTemplateKey;
use App\Events\DisputeResolvedEvent;
use App\Traits\EmailTemplateTrait;
use Illuminate\Support\Facades\Log;

class DisputeResolvedListener
{
    use EmailTemplateTrait;

    public function handle(DisputeResolvedEvent $event): void
    {
        $dispute = $event->dispute;
        $resolveType = $event->resolveType;

        // Eager-load relationships if not already loaded
        if (! $dispute->relationLoaded('buyer')) {
            $dispute->load(['buyer', 'vendor', 'reason']);
        }

        $buyer = $dispute->buyer;
        $vendor = $dispute->vendor;
        $orderId = (string) $dispute->order_id;
        $disputeId = (string) $dispute->id;

        $decisionLabel = $resolveType === 'refund'
            ? 'Refund Approved'
            : 'Payment Released to Vendor';

        $sharedData = [
            'templateName' => EmailTemplateKey::DISPUTE_RESOLVED,
            'orderId' => $orderId,
            'disputeId' => $disputeId,
            'dispute' => $dispute,
            'resolveType' => $resolveType,
        ];

        // 1. Notify buyer
        if ($buyer && $buyer->email) {
            try {
                $this->sendingMail(
                    sendMailTo: $buyer->email,
                    userType: 'customer',
                    templateName: EmailTemplateKey::DISPUTE_RESOLVED,
                    data: array_merge($sharedData, [
                        'userType' => 'customer',
                        'userName' => $buyer->name,
                        'subject' => 'Dispute #' . $disputeId . ' Resolved — ' . $decisionLabel,
                    ]),
                );
            } catch (\Throwable $e) {
                Log::error('[DisputeResolvedListener] Failed to send buyer email', ['error' => $e->getMessage()]);
            }
        }

        // 2. Notify vendor
        if ($vendor && $vendor->email) {
            try {
                $this->sendingMail(
                    sendMailTo: $vendor->email,
                    userType: 'vendor',
                    templateName: EmailTemplateKey::DISPUTE_RESOLVED,
                    data: array_merge($sharedData, [
                        'userType' => 'vendor',
                        'vendorName' => $vendor->name,
                        'subject' => 'Dispute #' . $disputeId . ' Resolved — ' . $decisionLabel,
                    ]),
                );
            } catch (\Throwable $e) {
                Log::error('[DisputeResolvedListener] Failed to send vendor email', ['error' => $e->getMessage()]);
            }
        }
    }
}
