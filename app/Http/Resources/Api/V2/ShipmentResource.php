<?php

namespace App\Http\Resources\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'qr_code' => $this->qr_code,
            'order' => [
                'id' => $this->order->id ?? null,
                'order_number' => $this->order->order_number ?? null,
                'customer_name' => $this->order->customer->name ?? null,
            ],
            'vehicle' => [
                'id' => $this->vehicle->id ?? null,
                'plate' => $this->vehicle->plate ?? null,
                'type' => $this->vehicle->vehicle_type ?? null,
            ],
            'driver' => [
                'id' => $this->driver->id ?? null,
                'name' => $this->driver->name ?? null,
                'phone' => $this->driver->phone ?? null,
            ],
            'schedule' => [
                'pickup_date' => $this->pickup_date?->toIso8601String(),
                'delivery_date' => $this->delivery_date?->toIso8601String(),
            ],
            'location' => $this->when(isset($this->current_location), [
                'latitude' => $this->current_location['lat'] ?? null,
                'longitude' => $this->current_location['lng'] ?? null,
                'updated_at' => $this->location_updated_at?->toIso8601String(),
            ]),
            'notes' => $this->notes,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
