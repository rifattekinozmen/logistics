<?php

namespace App\Driver\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V2\ShipmentResource;
use App\Models\Shipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverV2Controller extends Controller
{
    /**
     * Enhanced driver dashboard with location, today's shipments, and stats.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $driver = Auth::user()->employee;

        if (! $driver) {
            return response()->json([
                'success' => false,
                'message' => 'Driver profile not found',
            ], 404);
        }

        $todayShipments = Shipment::with(['order.customer', 'vehicle'])
            ->where('driver_id', $driver->id)
            ->whereDate('pickup_date', today())
            ->whereIn('status', ['pending', 'assigned', 'loaded', 'in_transit'])
            ->get();

        $stats = [
            'total_today' => $todayShipments->count(),
            'pending' => $todayShipments->where('status', 'pending')->count(),
            'in_transit' => $todayShipments->where('status', 'in_transit')->count(),
            'completed_this_week' => Shipment::where('driver_id', $driver->id)
                ->where('status', 'delivered')
                ->whereBetween('delivery_date', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'total_completed' => Shipment::where('driver_id', $driver->id)
                ->where('status', 'delivered')
                ->count(),
        ];

        $lastLocation = $driver->last_known_location ?? null;

        return response()->json([
            'success' => true,
            'data' => [
                'driver' => [
                    'id' => $driver->id,
                    'name' => $driver->name,
                    'phone' => $driver->phone,
                ],
                'stats' => $stats,
                'today_shipments' => ShipmentResource::collection($todayShipments),
                'last_location' => $lastLocation ? [
                    'latitude' => $lastLocation['latitude'] ?? null,
                    'longitude' => $lastLocation['longitude'] ?? null,
                    'updated_at' => $lastLocation['updated_at'] ?? null,
                ] : null,
            ],
        ]);
    }

    /**
     * Location-based check-in with timestamp.
     */
    public function checkIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'shipment_id' => 'nullable|exists:shipments,id',
        ]);

        $driver = Auth::user()->employee;

        if (! $driver) {
            return response()->json([
                'success' => false,
                'message' => 'Driver profile not found',
            ], 404);
        }

        $locationData = [
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'updated_at' => now()->toIso8601String(),
        ];

        $driver->update([
            'last_known_location' => $locationData,
        ]);

        if (isset($validated['shipment_id'])) {
            $shipment = Shipment::find($validated['shipment_id']);
            if ($shipment && $shipment->driver_id === $driver->id) {
                $shipment->update([
                    'current_location' => $locationData,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Check-in successful',
            'data' => [
                'timestamp' => now()->toIso8601String(),
                'location' => $locationData,
            ],
        ]);
    }
}
