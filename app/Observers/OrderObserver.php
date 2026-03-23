<?php

namespace App\Observers;

use App\Mail\DigitalCodeDeliveryMail;
use App\Models\DigitalProductCode;
use App\Models\Order;
use App\Services\DigitalProductCodeService;
use App\Traits\PushNotificationTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderObserver
{
    use PushNotificationTrait;

    public function __construct(private readonly DigitalProductCodeService $codeService) {}

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "updated" event.
     * When payment_status transitions to 'paid', assign digital codes to all eligible order details
     * and then email them to the customer.
     */
    public function updated(Order $order): void
    {
        if ($order->wasChanged('payment_status') && $order->payment_status === 'paid') {
            $order->loadMissing('orderDetails');
            $this->codeService->assignCodesForOrder($order);
            $this->sendDigitalCodeEmail($order);
        }
    }

    /**
     * Send all assigned digital codes for this order to the customer via email.
     */
    private function sendDigitalCodeEmail(Order $order): void
    {
        try {
            // Collect assigned codes for this order
            $assignedCodes = DigitalProductCode::query()
                ->where('order_id', $order->id)
                ->where('status', 'sold')
                ->with('product')
                ->get();

            if ($assignedCodes->isEmpty()) {
                return;
            }

            // Build code payload (decrypt only at delivery time)
            $codes = $assignedCodes->map(function (DigitalProductCode $record): array {
                return [
                    'productName' => $record->product?->name ?? translate('Digital Product'),
                    'code' => $record->decryptCode(),
                    'serial' => $record->serial_number,
                    'expiry' => $record->expiry_date?->format('Y-m-d'),
                ];
            })->all();

            // Resolve customer email + name
            if ($order->is_guest) {
                $addressData = $order->billing_address_data ?? $order->shipping_address_data;
                $email = $addressData?->email ?? null;
                $name = trim(($addressData?->contact_person_name ?? translate('Customer')));
            } else {
                $order->loadMissing('customer');
                $email = $order->customer?->email ?? null;
                $name = trim($order->customer?->f_name . ' ' . $order->customer?->l_name);
            }

            if (! $email) {
                return;
            }

            $companyName = getWebConfig(name: 'company_name') ?? config('app.name');

            $data = [
                'subject' => translate('Your Digital Codes — Order #') . $order->id,
                'customerName' => $name ?: translate('Customer'),
                'orderId' => $order->id,
                'orderDate' => $order->created_at?->format('Y-m-d'),
                'codes' => $codes,
                'companyName' => $companyName,
            ];

            Mail::to($email)->queue(new DigitalCodeDeliveryMail($data));
        } catch (\Throwable $e) {
            Log::error('OrderObserver: failed to send digital code email', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
