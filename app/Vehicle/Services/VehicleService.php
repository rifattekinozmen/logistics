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

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (isset($filters['vehicle_type'])) {
            $query->where('vehicle_type', $filters['vehicle_type']);
        }

        return $query->latest()->paginate($perPage);
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
