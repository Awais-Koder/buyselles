<?php

namespace App\Events;

use App\Models\ReferralCustomer;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerRegisteredViaReferralEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public ReferralCustomer $referralCustomer;

    public User $referredBy;

    public function __construct(ReferralCustomer $referralCustomer, User $referredBy)
    {
        $this->referralCustomer = $referralCustomer;
        $this->referredBy = $referredBy;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
