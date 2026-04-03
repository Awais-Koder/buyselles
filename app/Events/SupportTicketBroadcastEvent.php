<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportTicketBroadcastEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $ticketId,
        public readonly string $subject,
        public readonly string $customerName,
        public readonly bool $isNewTicket = true,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.support-tickets'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'new-support-ticket';
    }

    public function broadcastWith(): array
    {
        return [
            'ticket_id' => $this->ticketId,
            'subject' => $this->subject,
            'customer' => $this->customerName,
            'is_new' => $this->isNewTicket,
            'message' => $this->isNewTicket
                ? translate('New_support_ticket_from').' '.$this->customerName
                : $this->customerName.' '.translate('replied_to_ticket').' #'.$this->ticketId,
        ];
    }
}
