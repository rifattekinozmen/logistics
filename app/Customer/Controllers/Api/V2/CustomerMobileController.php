<?php

namespace App\Customer\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V2\OrderResource;
use App\Http\Resources\Api\V2\ShipmentResource;
use App\Models\Order;
use App\Models\Shipment;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerMobileController extends Controller
{
    /**
     * Real-time shipment tracking with ETA.
     */
    public function tracking(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => 'nullable|string',
            'shipment_id' => 'nullable|exists:shipments,id',
        ]);

        $customer = Auth::user()->customer;

        if (! $customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer profile not found',
            ], 404);
        }

        $query = Shipment::with(['order.customer', 'vehicle', 'driver'])
            ->whereHas('order', function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            });

        if (isset($validated['order_number'])) {
            $query->whereHas('order', function ($q) use ($validated) {
                $q->where('order_number', $validated['order_number']);
            });
        }

        if (isset($validated['shipment_id'])) {
            $query->where('id', $validated['shipment_id']);
        }

        $shipments = $query->whereIn('status', ['assigned', 'loaded', 'in_transit'])
            ->get();

        $trackingData = $shipments->map(function ($shipment) {
            $eta = null;
            if ($shipment->delivery_date) {
                $eta = $shipment->delivery_date->toIso8601String();
            }

            return [
                'shipment' => new ShipmentResource($shipment),
                'eta' => $eta,
                'current_location' => $shipment->current_location ?? null,
                'progress_percentage' => $this->calculateProgress($shipment),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $trackingData,
        ]);
    }

    /**
     * Simplified order creation flow.
     */
    public function quickCreate(Request $request): JsonResponse
    {
        $customer = Auth::user()->customer;

        if (! $customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer profile not found',
            ], 404);
        }

        $validated = $request->validate([
            'pickup_address' => 'required|string|max:500',
            'delivery_address' => 'required|string|max:500',
            'cargo_description' => 'required|string|max:500',
            'weight' => 'required|numeric|min:0',
            'volume' => 'nullable|numeric|min:0',
            'planned_pickup_date' => 'required|date',
            'planned_delivery_date' => 'required|date|after:planned_pickup_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $order = DB::transaction(function () use ($customer, $validated) {
                $orderNumber = 'ORD-'.now()->format('Ymd').'-'.str_pad(Order::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

                return Order::create([
                    'order_number' => $orderNumber,
                    'customer_id' => $customer->id,
                    'company_id' => $customer->company_id,
                    'pickup_address' => $validated['pickup_address'],
                    'delivery_address' => $validated['delivery_address'],
                    'cargo_description' => $validated['cargo_description'],
                    'weight' => $validated['weight'],
                    'volume' => $validated['volume'] ?? null,
                    'planned_pickup_date' => $validated['planned_pickup_date'],
                    'planned_delivery_date' => $validated['planned_delivery_date'],
                    'notes' => $validated['notes'] ?? null,
                    'status' => 'pending',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => new OrderResource($order->load('customer')),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate shipment progress percentage.
     */
    private function calculateProgress(Shipment $shipment): int
    {
        return match ($shipment->status) {
            'pending' => 10,
            'assigned' => 25,
            'loaded' => 40,
            'in_transit' => 70,
            'delivered' => 100,
            default => 0,
        };
    }
}
