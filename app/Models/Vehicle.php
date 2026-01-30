<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'plate',
        'brand',
        'model',
        'year',
        'vehicle_type',
        'capacity_kg',
        'capacity_m3',
        'status',
        'branch_id',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'capacity_kg' => 'decimal:2',
            'capacity_m3' => 'decimal:2',
            'status' => 'integer',
        ];
    }

    /**
     * Get the branch that owns the vehicle.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the inspections for the vehicle.
     */
    public function inspections(): HasMany
    {
        return $this->hasMany(VehicleInspection::class);
    }

    /**
     * Get the damages for the vehicle.
     */
    public function damages(): HasMany
    {
        return $this->hasMany(VehicleDamage::class);
    }

    /**
     * Get the work orders for the vehicle.
     */
    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    /**
     * Get the documents for the vehicle.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'documentable_id')
            ->where('documentable_type', self::class);
    }
}
