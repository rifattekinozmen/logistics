<?php

namespace App\Pricing\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PricingCondition extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'condition_type',
        'name',
        'route_origin',
        'route_destination',
        'weight_from',
        'weight_to',
        'distance_from',
        'distance_to',
        'price_per_kg',
        'price_per_km',
        'flat_rate',
        'min_charge',
        'currency',
        'vehicle_type',
        'valid_from',
        'valid_to',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'weight_from' => 'decimal:2',
            'weight_to' => 'decimal:2',
            'distance_from' => 'decimal:2',
            'distance_to' => 'decimal:2',
            'price_per_kg' => 'decimal:4',
            'price_per_km' => 'decimal:4',
            'flat_rate' => 'decimal:2',
            'min_charge' => 'decimal:2',
            'valid_from' => 'date',
            'valid_to' => 'date',
            'status' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isActive(): bool
    {
        return $this->status === 1;
    }

    public function isValidForDate(\Carbon\Carbon $date): bool
    {
        if ($this->valid_from && $date->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_to && $date->gt($this->valid_to)) {
            return false;
        }

        return true;
    }
}
