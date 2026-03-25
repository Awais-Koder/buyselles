<?php

namespace App\Listeners;

use App\Enums\EmailTemplateKey;
use App\Events\OrderStatusBroadcastEvent;
use App\Events\OrderStatusEvent;
use App\Models\ShippingAddress;
use App\Traits\EmailTemplateTrait;
use App\Traits\PushNotificationTrait;
use Exception;

class OrderStatusListener
{
    use EmailTemplateTrait, PushNotificationTrait;

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
        $this->broadcastStatusChange($event);

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

    private function broadcastStatusChange(OrderStatusEvent $event): void
    {
        try {
            $order = $event->order;
            $customerName = $order->is_guest
                ? translate('Guest')
                : trim(($order->customer->f_name ?? '').' '.($order->customer->l_name ?? ''));

            event(new OrderStatusBroadcastEvent(
                orderId: $order->id,
                status: $event->key,
                customerName: $customerName,
                sellerId: $order->seller_id ? (int) $order->seller_id : null,
                customerId: ! $order->is_guest ? (int) $order->customer_id : null,
            ));
        } catch (Exception $exception) {
            // Broadcast failure should not block order flow
        }
    }

    private function sendStatusEmail(OrderStatusEvent $event): void
    {
        $order = $event->order;

        $templateMap = [
            'confirmed' => EmailTemplateKey::ORDER_CONFIRMED,
            'processing' => EmailTemplateKey::ORDER_PROCESSING,
            'out_for_delivery' => EmailTemplateKey::ORDER_OUT_FOR_DELIVERY,
            'delivered' => EmailTemplateKey::ORDER_DELIVERED,
            'returned' => EmailTemplateKey::ORDER_RETURNED,
            'failed' => EmailTemplateKey::ORDER_FAILED,
            'canceled' => EmailTemplateKey::ORDER_CANCELED,
        ];

        $templateName = $templateMap[$event->key] ?? null;
        if (! $templateName) {
            return;
        }

        $email = $this->resolveCustomerEmail($order);
        if (! $email) {
            return;
        }

        $customerName = $order->is_guest
            ? translate('Valued_Customer')
            : trim(($order->customer->f_name ?? '').' '.($order->customer->l_name ?? ''));

        $data = [
            'userName' => $customerName,
            'userType' => 'customer',
            'templateName' => $templateName,
            'orderId' => $order->id,
            'subject' => translate('order').' #'.$order->id.' - '.translate('status_update'),
        ];

        try {
            $this->sendingMail(
                sendMailTo: $email,
                userType: 'customer',
                templateName: $templateName,
                data: $data,
            );
        } catch (Exception $exception) {
            // Silently fail — email delivery should not block order flow
        }
    }

    private function resolveCustomerEmail(mixed $order): ?string
    {
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

            return $email;
        }

        return $order->customer->email ?? null;
    }
}
