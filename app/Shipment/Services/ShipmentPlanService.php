<?php

namespace App\Shipment\Services;

use App\Models\Order;
use App\Models\ShipmentPlan;

class ShipmentPlanService
{
    public function createPlan(Order $order, array $data = []): ShipmentPlan
    {
        if ($order->shipmentPlans()->exists()) {
            return $order->shipmentPlans()->latest('id')->first();
        }

        return ShipmentPlan::create([
            'order_id' => $order->id,
            'vehicle_id' => $data['vehicle_id'] ?? null,
            'driver_id' => $data['driver_id'] ?? null,
            'planned_pickup_date' => $data['planned_pickup_date'] ?? $order->planned_pickup_date,
            'planned_delivery_date' => $data['planned_delivery_date'] ?? $order->planned_delivery_date,
            'status' => $data['status'] ?? 'planned',
            'notes' => $data['notes'] ?? null,
        ]);
    }
}

