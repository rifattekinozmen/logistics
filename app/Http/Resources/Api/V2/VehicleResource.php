<?php

namespace App\Http\Resources\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
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
            'plate' => $this->plate,
            'brand' => $this->brand,
            'model' => $this->model,
            'year' => (int) $this->year,
            'type' => $this->vehicle_type,
            'capacity' => [
                'weight_kg' => (float) $this->capacity_kg,
                'volume_m3' => (float) $this->capacity_m3,
            ],
            'status' => $this->status === 1 ? 'active' : 'inactive',
            'branch' => [
                'id' => $this->branch->id ?? null,
                'name' => $this->branch->name ?? null,
            ],
            'maintenance' => $this->when($this->relationLoaded('inspections'), [
                'last_inspection' => $this->inspections->sortByDesc('inspection_date')->first()?->inspection_date?->toIso8601String(),
                'next_inspection' => $this->next_inspection_date?->toIso8601String(),
            ]),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
