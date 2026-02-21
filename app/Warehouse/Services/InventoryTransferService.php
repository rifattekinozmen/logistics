<?php

namespace App\Warehouse\Services;

use App\Models\InventoryStock;
use Exception;
use Illuminate\Support\Facades\DB;

class InventoryTransferService
{
    /**
     * Transfer stock between warehouses.
     */
    public function transfer(array $data): array
    {
        $validated = $this->validateTransferData($data);

        return DB::transaction(function () use ($validated) {
            $sourceStock = InventoryStock::query()
                ->where('warehouse_id', $validated['from_warehouse_id'])
                ->where('item_id', $validated['item_id'])
                ->lockForUpdate()
                ->first();

            if (! $sourceStock || $sourceStock->quantity < $validated['quantity']) {
                throw new Exception('Yetersiz stok');
            }

            $sourceStock->decrement('quantity', $validated['quantity']);

            $targetStock = InventoryStock::firstOrCreate(
                [
                    'warehouse_id' => $validated['to_warehouse_id'],
                    'item_id' => $validated['item_id'],
                ],
                ['quantity' => 0]
            );

            $targetStock->increment('quantity', $validated['quantity']);

            return [
                'success' => true,
                'transfer_id' => uniqid('TRF-'),
                'from_stock' => [
                    'id' => $sourceStock->id,
                    'remaining_quantity' => $sourceStock->quantity,
                ],
                'to_stock' => [
                    'id' => $targetStock->id,
                    'new_quantity' => $targetStock->quantity,
                ],
            ];
        });
    }

    /**
     * Get critical stock alerts.
     */
    public function getCriticalStockAlerts(): array
    {
        $criticalStocks = InventoryStock::query()
            ->with(['item', 'warehouse'])
            ->get()
            ->filter(function ($stock) {
                $minLevel = $stock->item->min_stock_level ?? 10;

                return $stock->quantity <= $minLevel;
            });

        return $criticalStocks->map(function ($stock) {
            return [
                'item_id' => $stock->item_id,
                'item_name' => $stock->item->name,
                'sku' => $stock->item->sku,
                'warehouse_id' => $stock->warehouse_id,
                'warehouse_name' => $stock->warehouse->name,
                'current_quantity' => $stock->quantity,
                'min_level' => $stock->item->min_stock_level ?? 10,
                'severity' => $stock->quantity <= ($stock->item->min_stock_level ?? 10) / 2 ? 'high' : 'medium',
            ];
        })->values()->all();
    }

    /**
     * Validate transfer data.
     */
    protected function validateTransferData(array $data): array
    {
        if (! isset($data['from_warehouse_id'], $data['to_warehouse_id'], $data['item_id'], $data['quantity'])) {
            throw new Exception('Eksik transfer bilgisi');
        }

        if ($data['from_warehouse_id'] === $data['to_warehouse_id']) {
            throw new Exception('Aynı depoya transfer yapılamaz');
        }

        if ($data['quantity'] <= 0) {
            throw new Exception('Geçersiz miktar');
        }

        return $data;
    }
}
