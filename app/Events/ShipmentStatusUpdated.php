<?php

namespace App\Events;

use App\Models\Shipment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShipmentStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Shipment $shipment
    ) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('company.'.$this->shipment->order->company_id);
    }

    public function broadcastWith(): array
    {
        return [
            'shipment_id' => $this->shipment->id,
            'order_id' => $this->shipment->order_id,
            'status' => $this->shipment->status,
            'vehicle' => $this->shipment->vehicle?->plate_number,
            'driver' => $this->shipment->driver?->name,
            'updated_at' => $this->shipment->updated_at->toIso8601String(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'shipment.status.updated';
    }
}
