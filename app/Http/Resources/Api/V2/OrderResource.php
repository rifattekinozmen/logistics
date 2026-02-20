<?php

namespace App\Http\Resources\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_number' => $this->order_number,
            'status' => $this->status,
            'customer' => [
                'id' => $this->customer->id ?? null,
                'name' => $this->customer->name ?? null,
                'phone' => $this->customer->phone ?? null,
            ],
            'pickup' => [
                'address' => $this->pickup_address,
                'date' => $this->planned_pickup_date?->toIso8601String(),
                'actual_date' => $this->actual_pickup_date?->toIso8601String(),
            ],
            'delivery' => [
                'address' => $this->delivery_address,
                'date' => $this->planned_delivery_date?->toIso8601String(),
                'actual_date' => $this->delivered_at?->toIso8601String(),
            ],
            'cargo' => [
                'weight_kg' => (float) $this->total_weight,
                'volume_m3' => (float) $this->total_volume,
                'is_dangerous' => (bool) $this->is_dangerous,
            ],
            'pricing' => [
                'freight_price' => (float) ($this->freight_price ?? 0),
                'currency' => 'TRY',
            ],
            'notes' => $this->notes,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
