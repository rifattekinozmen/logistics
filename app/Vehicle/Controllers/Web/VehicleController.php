<?php

namespace App\Vehicle\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Vehicle\Requests\StoreVehicleRequest;
use App\Vehicle\Requests\UpdateVehicleRequest;
use App\Vehicle\Services\VehicleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function __construct(
        protected VehicleService $vehicleService
    ) {
    }

    /**
     * Display a listing of vehicles.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'branch_id', 'vehicle_type']);
        $vehicles = $this->vehicleService->getPaginated($filters);
        $branches = \App\Models\Branch::where('status', 1)->orderBy('name')->get();

        return view('admin.vehicles.index', compact('vehicles', 'branches'));
    }

    /**
     * Show the form for creating a new vehicle.
     */
    public function create(): View
    {
        $branches = \App\Models\Branch::where('status', 1)->orderBy('name')->get();

        return view('admin.vehicles.create', compact('branches'));
    }

    /**
     * Store a newly created vehicle.
     */
    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        $vehicle = $this->vehicleService->create($request->validated());

        return redirect()->route('admin.vehicles.show', $vehicle)
            ->with('success', 'Araç başarıyla oluşturuldu.');
    }

    /**
     * Display the specified vehicle.
     */
    public function show(int $id): View
    {
        $vehicle = \App\Models\Vehicle::with(['branch', 'inspections', 'damages', 'workOrders'])->findOrFail($id);

        return view('admin.vehicles.show', compact('vehicle'));
    }

    /**
     * Show the form for editing the specified vehicle.
     */
    public function edit(int $id): View
    {
        $vehicle = \App\Models\Vehicle::findOrFail($id);
        $branches = \App\Models\Branch::where('status', 1)->orderBy('name')->get();

        return view('admin.vehicles.edit', compact('vehicle', 'branches'));
    }

    /**
     * Update the specified vehicle.
     */
    public function update(UpdateVehicleRequest $request, int $id): RedirectResponse
    {
        $vehicle = \App\Models\Vehicle::findOrFail($id);

        $this->vehicleService->update($vehicle, $request->validated());

        return redirect()->route('admin.vehicles.show', $vehicle)
            ->with('success', 'Araç başarıyla güncellendi.');
    }

    /**
     * Remove the specified vehicle.
     */
    public function destroy(int $id): RedirectResponse
    {
        $vehicle = \App\Models\Vehicle::findOrFail($id);
        $vehicle->delete();

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Araç başarıyla silindi.');
    }
}
