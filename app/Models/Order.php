<?php

namespace App\Models;

use App\DocumentFlow\Models\DocumentFlow;
use App\Pricing\Models\PricingCondition;
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
        'sap_order_number',
        'status',
        'pickup_address',
        'delivery_address',
        'planned_pickup_date',
        'planned_delivery_date',
        'actual_pickup_date',
        'delivered_at',
        'planned_at',
        'invoiced_at',
        'total_weight',
        'total_volume',
        'is_dangerous',
        'notes',
        'created_by',
        'freight_price',
        'pricing_condition_id',
    ];

    protected function casts(): array
    {
        return [
            'planned_pickup_date' => 'datetime',
            'planned_delivery_date' => 'datetime',
            'actual_pickup_date' => 'datetime',
            'delivered_at' => 'datetime',
            'planned_at' => 'datetime',
            'invoiced_at' => 'datetime',
            'total_weight' => 'decimal:2',
            'total_volume' => 'decimal:2',
            'is_dangerous' => 'boolean',
            'freight_price' => 'decimal:2',
        ];
    }

    /**
     * Durumun Türkçe etiketini döner.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Beklemede',
            'planned' => 'Planlandı',
            'assigned' => 'Atandı',
            'loaded' => 'Yüklendi',
            'in_transit' => 'Yolda',
            'delivered' => 'Teslim Edildi',
            'invoiced' => 'Faturalandı',
            'cancelled' => 'İptal',
            default => ucfirst($this->status),
        };
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

    public function pricingCondition(): BelongsTo
    {
        return $this->belongsTo(PricingCondition::class);
    }

    /**
     * Bu siparişin başlattığı doküman akışı adımları.
     */
    public function documentFlows(): HasMany
    {
        return $this->hasMany(DocumentFlow::class, 'source_id')
            ->where('source_type', self::class);
    }
}
