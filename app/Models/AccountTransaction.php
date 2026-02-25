<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountTransaction extends Model
{
    use HasFactory;

    public const TYPE_DEBIT = 'debit';

    public const TYPE_CREDIT = 'credit';

    public const TYPE_ADJUSTMENT = 'adjustment';

    protected $fillable = [
        'customer_id',
        'payment_id',
        'e_invoice_id',
        'type',
        'amount',
        'balance_after',
        'currency',
        'description',
        'transaction_date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'transaction_date' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function eInvoice(): BelongsTo
    {
        return $this->belongsTo(\App\EInvoice\Models\EInvoice::class);
    }
}
