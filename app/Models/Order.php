<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'order_number',
        'status',
        'pickup_address',
        'delivery_address',
        'planned_pickup_date',
        'planned_delivery_date',
        'actual_pickup_date',
        'delivered_at',
        'total_weight',
        'total_volume',
        'is_dangerous',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'planned_pickup_date' => 'datetime',
            'planned_delivery_date' => 'datetime',
            'actual_pickup_date' => 'datetime',
            'delivered_at' => 'datetime',
            'total_weight' => 'decimal:2',
            'total_volume' => 'decimal:2',
            'is_dangerous' => 'boolean',
        ];
    }

    /**
     * Get the customer that owns the order.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user who created the order.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the shipments for the order.
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    /**
     * Get the delivery numbers for the order.
     */
    public function deliveryNumbers(): HasMany
    {
        return $this->hasMany(DeliveryNumber::class);
    }

    /**
     * Get the documents for the order.
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
