<?php

namespace App\Vehicle\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Vehicle\Services\VehicleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function __construct(
        protected VehicleService $vehicleService
    ) {
    }

    /**
     * Display a listing of vehicles.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'branch_id', 'vehicle_type']);
        $vehicles = $this->vehicleService->getPaginated($filters);

        return response()->json($vehicles);
    }

    /**
     * Store a newly created vehicle.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plate' => 'required|string|max:20|unique:vehicles,plate',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'nullable|integer|min:1900|max:'.date('Y'),
            'vehicle_type' => 'required|string|in:truck,van,car,trailer',
            'capacity_kg' => 'nullable|numeric|min:0',
            'capacity_m3' => 'nullable|numeric|min:0',
            'status' => 'required|integer|in:0,1,2',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $vehicle = $this->vehicleService->create($validated);

        return response()->json($vehicle, 201);
    }

    /**
     * Display the specified vehicle.
     */
    public function show(int $id): JsonResponse
    {
        $vehicle = \App\Models\Vehicle::with(['branch', 'inspections', 'damages'])->findOrFail($id);

        return response()->json($vehicle);
    }

    /**
     * Update the specified vehicle.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $vehicle = \App\Models\Vehicle::findOrFail($id);

        $validated = $request->validate([
            'plate' => 'required|string|max:20|unique:vehicles,plate,'.$vehicle->id,
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'nullable|integer|min:1900|max:'.date('Y'),
            'vehicle_type' => 'required|string|in:truck,van,car,trailer',
            'capacity_kg' => 'nullable|numeric|min:0',
            'capacity_m3' => 'nullable|numeric|min:0',
            'status' => 'required|integer|in:0,1,2',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $vehicle = $this->vehicleService->update($vehicle, $validated);

        return response()->json($vehicle);
    }

    /**
     * Remove the specified vehicle.
     */
    public function destroy(int $id): JsonResponse
    {
        $vehicle = \App\Models\Vehicle::findOrFail($id);
        $vehicle->delete();

        return response()->json(['message' => 'Araç başarıyla silindi.'], 200);
    }
}
