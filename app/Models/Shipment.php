<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'vehicle_id',
        'driver_id',
        'status',
        'pickup_date',
        'delivery_date',
        'qr_code',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'pickup_date' => 'datetime',
            'delivery_date' => 'datetime',
        ];
    }

    /**
     * Get the order that owns the shipment.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the vehicle for the shipment.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the driver for the shipment.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'driver_id');
    }
}
