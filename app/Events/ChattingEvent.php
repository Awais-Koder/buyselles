<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChattingEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public string $key, public string $type, public object|array $userData, public object|array $messageForm)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return match ($this->type) {
            'seller' => is_object($this->userData) && isset($this->userData->id)
                ? [new PrivateChannel('seller.'.$this->userData->id.'.chat')]
                : [],
            'admin' => [new PrivateChannel('admin.chat')],
            default => [],
        };
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'chatting';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return ['message' => translate('New_Message')];
    }
}
