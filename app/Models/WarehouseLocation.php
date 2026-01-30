<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseLocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'warehouse_id',
        'parent_id',
        'location_type',
        'code',
        'name',
        'full_path',
        'capacity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'decimal:2',
            'status' => 'integer',
        ];
    }

    /**
     * Get the warehouse that owns the location.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the parent location.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'parent_id');
    }

    /**
     * Get the child locations.
     */
    public function children(): HasMany
    {
        return $this->hasMany(WarehouseLocation::class, 'parent_id');
    }

    /**
     * Get the stocks for the location.
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class, 'location_id');
    }
}
