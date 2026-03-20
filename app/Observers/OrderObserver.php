<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\DigitalProductCodeService;
use App\Traits\PushNotificationTrait;

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
     * When payment_status transitions to 'paid', assign digital codes to all eligible order details.
     */
    public function updated(Order $order): void
    {
        if ($order->wasChanged('payment_status') && $order->payment_status === 'paid') {
            $order->loadMissing('orderDetails');
            $this->codeService->assignCodesForOrder($order);
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
