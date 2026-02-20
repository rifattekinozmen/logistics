<?php

namespace App\Notifications;

use App\Models\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class ShipmentAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Shipment $shipment
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'push'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'shipment_assigned',
            'shipment_id' => $this->shipment->id,
            'order_number' => $this->shipment->order->order_number,
            'vehicle' => $this->shipment->vehicle?->plate_number,
            'pickup_date' => $this->shipment->pickup_date?->toIso8601String(),
            'delivery_date' => $this->shipment->delivery_date?->toIso8601String(),
            'title' => 'Yeni Sevkiyat Ataması',
            'message' => "Sipariş #{$this->shipment->order->order_number} için yeni bir sevkiyat atandı.",
        ];
    }

    public function toPush(object $notifiable): array
    {
        return [
            'title' => 'Yeni Sevkiyat Ataması',
            'body' => "Sipariş #{$this->shipment->order->order_number} için yeni bir sevkiyat atandı.",
            'data' => [
                'shipment_id' => $this->shipment->id,
                'order_id' => $this->shipment->order_id,
                'type' => 'shipment_assigned',
                'action_url' => route('admin.shipments.show', $this->shipment->id),
            ],
            'badge' => 1,
            'sound' => 'default',
        ];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        return new DatabaseMessage($this->toArray($notifiable));
    }
}
