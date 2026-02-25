<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Models\ShipmentPlan;

class CreateShipmentPlanForPaidOrder
{
    public function handle(OrderPaid $event): void
    {
        $order = $event->order->fresh();

        if (! $order) {
            return;
        }

        // Zaten plan varsa tekrar oluşturma.
        if ($order->shipmentPlans()->exists()) {
            return;
        }

        ShipmentPlan::create([
            'order_id' => $order->id,
            'vehicle_id' => null,
            'driver_id' => null,
            'planned_pickup_date' => $order->planned_pickup_date,
            'planned_delivery_date' => $order->planned_delivery_date,
            'status' => 'planned',
            'notes' => 'Order paid - automatic shipment plan created.',
        ]);
    }
}
