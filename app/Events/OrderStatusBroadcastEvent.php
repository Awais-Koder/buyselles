<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusBroadcastEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $orderId,
        public string $status,
        public string $customerName,
        public ?int $sellerId = null,
        public ?int $customerId = null,
    ) {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('admin.orders'),
        ];

        if ($this->sellerId) {
            $channels[] = new PrivateChannel('seller.'.$this->sellerId.'.orders');
        }

        if ($this->customerId) {
            $channels[] = new PrivateChannel('customer.'.$this->customerId.'.orders');
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'order-status-changed';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->orderId,
            'status' => $this->status,
            'customer_name' => $this->customerName,
            'message' => translate('Order').' #'.$this->orderId.' — '.translate(ucfirst(str_replace('_', ' ', $this->status))),
        ];
    }
}
