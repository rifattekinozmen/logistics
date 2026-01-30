<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'related_type',
        'related_id',
        'payment_type',
        'amount',
        'due_date',
        'paid_date',
        'status',
        'payment_method',
        'reference_number',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_date' => 'date',
            'status' => 'integer',
        ];
    }

    /**
     * Get the parent related model.
     */
    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created the payment.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
