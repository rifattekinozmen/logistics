<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order
    ) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('company.'.$this->order->company_id);
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'customer' => $this->order->customer?->name,
            'status' => $this->order->status,
            'pickup_address' => $this->order->pickup_address,
            'delivery_address' => $this->order->delivery_address,
            'created_at' => $this->order->created_at->toIso8601String(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.created';
    }
}
