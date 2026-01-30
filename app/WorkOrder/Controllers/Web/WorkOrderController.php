<?php

namespace App\WorkOrder\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkOrderController extends Controller
{
    /**
     * Display a listing of work orders.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'vehicle_id', 'service_provider_id', 'date_from', 'date_to']);
        $workOrders = \App\Models\WorkOrder::query()
            ->with(['vehicle', 'serviceProvider'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['vehicle_id'] ?? null, fn ($q, $vehicleId) => $q->where('vehicle_id', $vehicleId))
            ->when($filters['service_provider_id'] ?? null, fn ($q, $providerId) => $q->where('service_provider_id', $providerId))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        $vehicles = \App\Models\Vehicle::where('status', 1)->orderBy('plate')->get();
        $serviceProviders = \App\Models\ServiceProvider::where('status', 1)->orderBy('name')->get();

        return view('admin.work-orders.index', compact('workOrders', 'vehicles', 'serviceProviders'));
    }

    /**
     * Show the form for creating a new work order.
     */
    public function create(): View
    {
        $vehicles = \App\Models\Vehicle::where('status', 1)->orderBy('plate')->get();
        $serviceProviders = \App\Models\ServiceProvider::where('status', 1)->orderBy('name')->get();

        return view('admin.work-orders.create', compact('vehicles', 'serviceProviders'));
    }

    /**
     * Store a newly created work order.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'service_provider_id' => 'nullable|exists:service_providers,id',
            'type' => 'required|string|max:100',
            'description' => 'nullable|string|max:2000',
            'status' => 'required|string|max:50',
        ]);

        $workOrder = \App\Models\WorkOrder::create($validated);

        return redirect()->route('admin.work-orders.show', $workOrder)
            ->with('success', 'İş emri başarıyla oluşturuldu.');
    }

    /**
     * Display the specified work order.
     */
    public function show(int $id): View
    {
        $workOrder = \App\Models\WorkOrder::with(['vehicle', 'serviceProvider'])->findOrFail($id);

        return view('admin.work-orders.show', compact('workOrder'));
    }

    /**
     * Show the form for editing the specified work order.
     */
    public function edit(int $id): View
    {
        $workOrder = \App\Models\WorkOrder::findOrFail($id);
        $vehicles = \App\Models\Vehicle::where('status', 1)->orderBy('plate')->get();
        $serviceProviders = \App\Models\ServiceProvider::where('status', 1)->orderBy('name')->get();

        return view('admin.work-orders.edit', compact('workOrder', 'vehicles', 'serviceProviders'));
    }

    /**
     * Update the specified work order.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $workOrder = \App\Models\WorkOrder::findOrFail($id);

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'service_provider_id' => 'nullable|exists:service_providers,id',
            'type' => 'required|string|max:100',
            'description' => 'nullable|string|max:2000',
            'status' => 'required|string|max:50',
        ]);

        $workOrder->update($validated);

        return redirect()->route('admin.work-orders.show', $workOrder)
            ->with('success', 'İş emri başarıyla güncellendi.');
    }

    /**
     * Remove the specified work order.
     */
    public function destroy(int $id): RedirectResponse
    {
        $workOrder = \App\Models\WorkOrder::findOrFail($id);
        $workOrder->delete();

        return redirect()->route('admin.work-orders.index')
            ->with('success', 'İş emri başarıyla silindi.');
    }
}
