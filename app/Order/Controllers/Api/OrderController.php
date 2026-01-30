<?php

namespace App\Order\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Order\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {
    }

    /**
     * Display a listing of orders.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'customer_id', 'date_from', 'date_to']);
        $orders = $this->orderService->getPaginated($filters);

        return response()->json($orders);
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'pickup_address' => 'required|string|max:1000',
            'delivery_address' => 'required|string|max:1000',
            'planned_pickup_date' => 'nullable|date',
            'planned_delivery_date' => 'nullable|date|after:planned_pickup_date',
            'total_weight' => 'nullable|numeric|min:0',
            'total_volume' => 'nullable|numeric|min:0',
            'is_dangerous' => 'boolean',
            'notes' => 'nullable|string|max:2000',
        ]);

        $order = $this->orderService->create($validated, $request->user());

        return response()->json($order, 201);
    }

    /**
     * Display the specified order.
     */
    public function show(int $id): JsonResponse
    {
        $order = \App\Models\Order::with(['customer', 'shipments', 'creator'])->findOrFail($id);

        return response()->json($order);
    }

    /**
     * Update the specified order.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $order = \App\Models\Order::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|string|in:pending,assigned,in_transit,delivered,cancelled',
            'pickup_address' => 'required|string|max:1000',
            'delivery_address' => 'required|string|max:1000',
            'planned_pickup_date' => 'nullable|date',
            'planned_delivery_date' => 'nullable|date|after:planned_pickup_date',
            'total_weight' => 'nullable|numeric|min:0',
            'total_volume' => 'nullable|numeric|min:0',
            'is_dangerous' => 'boolean',
            'notes' => 'nullable|string|max:2000',
        ]);

        $order = $this->orderService->update($order, $validated);

        return response()->json($order);
    }

    /**
     * Remove the specified order.
     */
    public function destroy(int $id): JsonResponse
    {
        $order = \App\Models\Order::findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'Sipariş başarıyla silindi.'], 200);
    }
}
