<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelPrice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'price_date',
        'price_type',
        'price',
        'supplier_name',
        'region',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'price_date' => 'date',
            'price' => 'decimal:4',
        ];
    }

    /**
     * Get the company that owns the fuel price.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who created the fuel price.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
