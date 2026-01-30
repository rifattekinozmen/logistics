<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryNumber extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'delivery_number',
        'customer_name',
        'customer_phone',
        'delivery_address',
        'location_id',
        'order_id',
        'status',
        'error_message',
        'import_batch_id',
        'row_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'row_number' => 'integer',
        ];
    }

    /**
     * Get the company that owns the delivery number.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the location for the delivery number.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the order for the delivery number.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the import batch for the delivery number.
     */
    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(DeliveryImportBatch::class, 'import_batch_id');
    }
}
