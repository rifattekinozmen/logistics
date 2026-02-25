<?php

namespace App\Vehicle\Services;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class VehicleService
{
    /**
     * Create a new vehicle.
     */
    public function create(array $data): Vehicle
    {
        return Vehicle::create($data);
    }

    /**
     * Update an existing vehicle.
     */
    public function update(Vehicle $vehicle, array $data): Vehicle
    {
        $vehicle->update($data);

        return $vehicle->fresh();
    }

    /**
     * Get paginated vehicles.
     */
    public function getPaginated(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = Vehicle::query()->with(['branch']);

        $sort = $filters['sort'] ?? null;
        $direction = ($filters['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        $sortableColumns = [
            'plate' => 'plate',
            'brand' => 'brand',
            'year' => 'year',
            'capacity_kg' => 'capacity_kg',
            'status' => 'status',
            'created_at' => 'created_at',
        ];

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (isset($filters['vehicle_type'])) {
            $query->where('vehicle_type', $filters['vehicle_type']);
        }

        if ($sort && \array_key_exists($sort, $sortableColumns)) {
            $query->orderBy($sortableColumns[$sort], $direction);
        } else {
            $query->latest();
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Filtrelenmiş araçları export için getir.
     */
    public function getForExport(array $filters = []): Collection
    {
        $query = Vehicle::query()->with(['branch']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (isset($filters['vehicle_type'])) {
            $query->where('vehicle_type', $filters['vehicle_type']);
        }

        return $query->latest()->get();
    }
}
