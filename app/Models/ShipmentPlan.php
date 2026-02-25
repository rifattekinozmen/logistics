<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShipmentPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'vehicle_id',
        'driver_id',
        'planned_pickup_date',
        'planned_delivery_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'planned_pickup_date' => 'datetime',
            'planned_delivery_date' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'driver_id');
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}
