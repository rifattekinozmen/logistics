<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'name',
        'pickup_address',
        'delivery_address',
        'total_weight',
        'total_volume',
        'is_dangerous',
        'notes',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'total_weight' => 'decimal:2',
            'total_volume' => 'decimal:2',
            'is_dangerous' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the customer that owns the order template.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
