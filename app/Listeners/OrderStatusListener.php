<?php

namespace App\Listeners;

use App\Events\OrderStatusEvent;
use App\Mail\OrderStatusChanged;
use App\Models\Order;
use App\Models\ShippingAddress;
use App\Traits\PushNotificationTrait;
use Exception;
use Illuminate\Support\Facades\Mail;

class OrderStatusListener
{
    use PushNotificationTrait;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderStatusEvent $event): void
    {
        $this->sendNotification($event);

        if ($event->type === 'customer') {
            $this->sendStatusEmail($event);
        }
    }

    private function sendNotification(OrderStatusEvent $event): void
    {
        $key = $event->key;
        $type = $event->type;
        $order = $event->order;
        $this->sendOrderNotification(key: $key, type: $type, order: $order);
    }

    private function sendStatusEmail(OrderStatusEvent $event): void
    {
        $emailServicesSmtp = getWebConfig(name: 'mail_config');
        if ($emailServicesSmtp['status'] == 0) {
            $emailServicesSmtp = getWebConfig(name: 'mail_config_sendgrid');
        }

        if ($emailServicesSmtp['status'] != 1) {
            return;
        }

        $order = $event->order;
        $email = null;

        if ($order->is_guest) {
            $shippingAddress = is_string($order->shipping_address_data)
                ? json_decode($order->shipping_address_data)
                : $order->shipping_address_data;
            $email = $shippingAddress->email ?? null;

            if (! $email) {
                $billingAddress = is_string($order->billing_address_data)
                    ? json_decode($order->billing_address_data)
                    : $order->billing_address_data;
                $email = $billingAddress->email ?? null;
            }

            if (! $email && $order->shipping_address) {
                $address = ShippingAddress::find($order->shipping_address);
                $email = $address->email ?? null;
            }
        } else {
            $customer = $order->customer ?? null;
            $email = $customer->email ?? null;
        }

        if (! $email) {
            return;
        }

        $statusMap = [
            'order_pending_message' => 'pending',
            'order_confirmation_message' => 'confirmed',
            'order_processing_message' => 'processing',
            'out_for_delivery_message' => 'out_for_delivery',
            'order_delivered_message' => 'delivered',
            'order_returned_message' => 'returned',
            'order_failed_message' => 'failed',
            'order_canceled' => 'canceled',
        ];

        $orderStatus = $statusMap[$event->key] ?? $event->key;

        try {
            Mail::to($email)->send(new OrderStatusChanged(
                orderId: (int) $order->id,
                orderStatus: $orderStatus
            ));
        } catch (Exception $exception) {
            // Silently fail — email delivery should not block order flow
        }
    }
}
