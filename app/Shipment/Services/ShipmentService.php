<?php

namespace App\Shipment\Services;

use App\Events\ShipmentDelivered;
use App\Models\Shipment;
use App\Models\ShipmentPlan;
use Illuminate\Database\DatabaseManager;

class ShipmentService
{
    public function __construct(protected DatabaseManager $db) {}

    public function startShipment(ShipmentPlan $plan, array $data): Shipment
    {
        return $this->db->transaction(function () use ($plan, $data): Shipment {
            $shipment = $plan->shipments()->create([
                'order_id' => $plan->order_id,
                'vehicle_id' => $data['vehicle_id'] ?? $plan->vehicle_id,
                'driver_id' => $data['driver_id'] ?? $plan->driver_id,
                'status' => $data['status'] ?? 'in_transit',
                'pickup_date' => $data['pickup_date'] ?? now(),
                'delivery_date' => $data['delivery_date'] ?? null,
                'qr_code' => $data['qr_code'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $plan->status = 'in_transit';
            $plan->save();

            return $shipment;
        });
    }

    public function markDelivered(Shipment $shipment, array $data = []): Shipment
    {
        return $this->db->transaction(function () use ($shipment, $data): Shipment {
            $shipment->fill([
                'status' => 'delivered',
                'delivery_date' => $data['delivery_date'] ?? now(),
            ]);

            $shipment->save();

            if ($shipment->shipmentPlan) {
                $shipment->shipmentPlan->status = 'delivered';
                $shipment->shipmentPlan->save();
            }

            event(new ShipmentDelivered($shipment->fresh()));

            return $shipment->fresh();
        });
    }
}
