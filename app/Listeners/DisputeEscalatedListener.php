<?php

namespace App\Listeners;

use App\Enums\EmailTemplateKey;
use App\Events\DisputeEscalatedEvent;
use App\Models\Admin;
use App\Traits\EmailTemplateTrait;
use Illuminate\Support\Facades\Log;

class DisputeEscalatedListener
{
    use EmailTemplateTrait;

    public function handle(DisputeEscalatedEvent $event): void
    {
        $dispute = $event->dispute;
        $escalatedByType = $event->escalatedByType;

        // Eager-load relationships if not already loaded
        if (! $dispute->relationLoaded('buyer')) {
            $dispute->load(['buyer', 'vendor', 'reason']);
        }

        $buyer = $dispute->buyer;
        $vendor = $dispute->vendor;
        $orderId = (string) $dispute->order_id;
        $disputeId = (string) $dispute->id;

        $sharedData = [
            'templateName' => EmailTemplateKey::DISPUTE_ESCALATED,
            'orderId' => $orderId,
            'disputeId' => $disputeId,
            'dispute' => $dispute,
            'escalatedByType' => $escalatedByType,
        ];

        // 1. Notify admin via company email
        $adminEmail = getWebConfig(name: 'company_email');
        $adminName = getWebConfig(name: 'company_name') ?? 'Admin';
        if ($adminEmail) {
            try {
                $this->sendingMail(
                    sendMailTo: $adminEmail,
                    userType: 'admin',
                    templateName: EmailTemplateKey::DISPUTE_ESCALATED,
                    data: array_merge($sharedData, [
                        'userType' => 'admin',
                        'adminName' => $adminName,
                        'subject' => 'Dispute #' . $disputeId . ' Escalated to Admin — Order #' . $orderId,
                    ]),
                );
            } catch (\Throwable $e) {
                Log::error('[DisputeEscalatedListener] Failed to send admin email', ['error' => $e->getMessage()]);
            }
        }

        // 2. Notify buyer (escalation confirmation or notification)
        if ($buyer && $buyer->email) {
            try {
                $this->sendingMail(
                    sendMailTo: $buyer->email,
                    userType: 'customer',
                    templateName: EmailTemplateKey::DISPUTE_ESCALATED,
                    data: array_merge($sharedData, [
                        'userType' => 'customer',
                        'userName' => $buyer->name,
                        'subject' => 'Dispute #' . $disputeId . ' is Now Under Admin Review',
                    ]),
                );
            } catch (\Throwable $e) {
                Log::error('[DisputeEscalatedListener] Failed to send buyer email', ['error' => $e->getMessage()]);
            }
        }

        // 3. Notify vendor
        if ($vendor && $vendor->email) {
            try {
                $this->sendingMail(
                    sendMailTo: $vendor->email,
                    userType: 'vendor',
                    templateName: EmailTemplateKey::DISPUTE_ESCALATED,
                    data: array_merge($sharedData, [
                        'userType' => 'vendor',
                        'vendorName' => $vendor->name,
                        'subject' => 'Dispute #' . $disputeId . ' Escalated to Admin — Order #' . $orderId,
                    ]),
                );
            } catch (\Throwable $e) {
                Log::error('[DisputeEscalatedListener] Failed to send vendor email', ['error' => $e->getMessage()]);
            }
        }
    }
}
