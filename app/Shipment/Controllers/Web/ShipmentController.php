<?php

namespace App\Shipment\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShipmentController extends Controller
{
    /**
     * Display a listing of shipments.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'order_id', 'vehicle_id', 'date_from', 'date_to']);
        $shipments = \App\Models\Shipment::query()
            ->with(['order', 'vehicle', 'driver'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['order_id'] ?? null, fn ($q, $orderId) => $q->where('order_id', $orderId))
            ->when($filters['vehicle_id'] ?? null, fn ($q, $vehicleId) => $q->where('vehicle_id', $vehicleId))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('planned_pickup_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('planned_pickup_date', '<=', $date))
            ->orderBy('planned_pickup_date', 'desc')
            ->paginate(25);

        return view('admin.shipments.index', compact('shipments'));
    }

    /**
     * Show the form for creating a new shipment.
     */
    public function create(): View
    {
        $orders = \App\Models\Order::where('status', '!=', 'cancelled')->orderBy('id', 'desc')->get();
        $vehicles = \App\Models\Vehicle::where('status', 1)->orderBy('plate')->get();

        return view('admin.shipments.create', compact('orders', 'vehicles'));
    }

    /**
     * Store a newly created shipment.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'nullable|exists:employees,id',
            'planned_pickup_date' => 'required|date',
            'planned_delivery_date' => 'nullable|date|after:planned_pickup_date',
            'status' => 'required|string|max:50',
        ]);

        $shipment = \App\Models\Shipment::create($validated);

        return redirect()->route('admin.shipments.show', $shipment)
            ->with('success', 'Sevkiyat başarıyla oluşturuldu.');
    }

    /**
     * Display the specified shipment.
     */
    public function show(int $id): View
    {
        $shipment = \App\Models\Shipment::with(['order', 'vehicle', 'driver'])->findOrFail($id);

        return view('admin.shipments.show', compact('shipment'));
    }

    /**
     * Show the form for editing the specified shipment.
     */
    public function edit(int $id): View
    {
        $shipment = \App\Models\Shipment::findOrFail($id);
        $orders = \App\Models\Order::where('status', '!=', 'cancelled')->orderBy('id', 'desc')->get();
        $vehicles = \App\Models\Vehicle::where('status', 1)->orderBy('plate')->get();

        return view('admin.shipments.edit', compact('shipment', 'orders', 'vehicles'));
    }

    /**
     * Update the specified shipment.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $shipment = \App\Models\Shipment::findOrFail($id);

        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'nullable|exists:employees,id',
            'planned_pickup_date' => 'required|date',
            'planned_delivery_date' => 'nullable|date|after:planned_pickup_date',
            'status' => 'required|string|max:50',
        ]);

        $shipment->update($validated);

        return redirect()->route('admin.shipments.show', $shipment)
            ->with('success', 'Sevkiyat başarıyla güncellendi.');
    }

    /**
     * Remove the specified shipment.
     */
    public function destroy(int $id): RedirectResponse
    {
        $shipment = \App\Models\Shipment::findOrFail($id);
        $shipment->delete();

        return redirect()->route('admin.shipments.index')
            ->with('success', 'Sevkiyat başarıyla silindi.');
    }
}
