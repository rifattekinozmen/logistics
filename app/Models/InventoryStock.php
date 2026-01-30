<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'location_id',
        'item_id',
        'quantity',
        'serial_number',
        'lot_number',
        'expiry_date',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'expiry_date' => 'date',
        ];
    }

    /**
     * Get the warehouse that owns the stock.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the location for the stock.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'location_id');
    }

    /**
     * Get the item for the stock.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }
}
