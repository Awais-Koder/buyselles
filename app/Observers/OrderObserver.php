<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\DigitalProductCodeService;
use App\Traits\PushNotificationTrait;
use Illuminate\Support\Facades\Log;

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
            try {
                $this->codeService->assignAndNotify($order);
            } catch (\Throwable $e) {
                Log::error('OrderObserver: digital code assignment/email failed', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
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
