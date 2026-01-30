<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'sku',
        'barcode',
        'name',
        'category',
        'unit',
        'min_stock_level',
        'max_stock_level',
        'critical_stock_level',
        'track_serial',
        'track_lot',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'min_stock_level' => 'decimal:2',
            'max_stock_level' => 'decimal:2',
            'critical_stock_level' => 'decimal:2',
            'track_serial' => 'boolean',
            'track_lot' => 'boolean',
            'status' => 'integer',
        ];
    }

    /**
     * Get the company that owns the item.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the stocks for the item.
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class, 'item_id');
    }
}
