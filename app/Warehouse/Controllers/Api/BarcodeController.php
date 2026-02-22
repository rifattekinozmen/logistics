<?php

namespace App\Warehouse\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryStock;
use App\Warehouse\Services\InventoryTransferService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    /**
     * Barkod okut ve stok bilgisini döndür.
     */
    public function scan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'barcode' => 'required|string|max:100',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $item = InventoryItem::where('barcode', $validated['barcode'])->first();

        if (! $item) {
            return response()->json([
                'success' => false,
                'message' => 'Barkod bulunamadı.',
            ], 404);
        }

        // Stok bilgisini çek
        $stock = InventoryStock::where('warehouse_id', $validated['warehouse_id'])
            ->where('item_id', $item->id)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'item' => [
                    'id' => $item->id,
                    'sku' => $item->sku,
                    'name' => $item->name,
                    'barcode' => $item->barcode,
                    'unit' => $item->unit,
                ],
                'stock' => $stock ? [
                    'quantity' => $stock->quantity,
                    'location' => $stock->location?->full_path ?? null,
                ] : null,
            ],
        ]);
    }

    /**
     * Stok girişi (barkod ile).
     */
    public function stockIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'barcode' => 'required|string|max:100',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0.01',
            'location_id' => 'nullable|exists:warehouse_locations,id',
        ]);

        $item = InventoryItem::where('barcode', $validated['barcode'])->first();

        if (! $item) {
            return response()->json([
                'success' => false,
                'message' => 'Barkod bulunamadı.',
            ], 404);
        }

        // Stok kaydını bul veya oluştur
        $stock = InventoryStock::firstOrCreate(
            [
                'warehouse_id' => $validated['warehouse_id'],
                'item_id' => $item->id,
                'location_id' => $validated['location_id'] ?? null,
            ],
            ['quantity' => 0]
        );

        // Stok miktarını artır
        $stock->increment('quantity', $validated['quantity']);

        return response()->json([
            'success' => true,
            'message' => 'Stok girişi yapıldı.',
            'data' => [
                'stock_id' => $stock->id,
                'new_quantity' => $stock->quantity,
            ],
        ]);
    }

    /**
     * Stok çıkışı (barkod ile).
     */
    public function stockOut(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'barcode' => 'required|string|max:100',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0.01',
            'location_id' => 'nullable|exists:warehouse_locations,id',
        ]);

        $item = InventoryItem::where('barcode', $validated['barcode'])->first();

        if (! $item) {
            return response()->json([
                'success' => false,
                'message' => 'Barkod bulunamadı.',
            ], 404);
        }

        $stock = InventoryStock::where('warehouse_id', $validated['warehouse_id'])
            ->where('item_id', $item->id)
            ->when($validated['location_id'], fn ($q) => $q->where('location_id', $validated['location_id']))
            ->first();

        if (! $stock || $stock->quantity < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => 'Yetersiz stok.',
            ], 400);
        }

        // Stok miktarını azalt
        $stock->decrement('quantity', $validated['quantity']);

        return response()->json([
            'success' => true,
            'message' => 'Stok çıkışı yapıldı.',
            'data' => [
                'stock_id' => $stock->id,
                'new_quantity' => $stock->quantity,
            ],
        ]);
    }

    /**
     * Stok transferi (depo → depo).
     */
    public function transfer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|numeric|min:0.01',
        ]);

        try {
            $result = $this->transferService->transfer($validated);

            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Kritik stok uyarıları.
     */
    public function criticalStockAlerts(): JsonResponse
    {
        $alerts = $this->transferService->getCriticalStockAlerts();

        return response()->json([
            'success' => true,
            'alerts' => $alerts,
            'count' => count($alerts),
        ]);
    }
}
