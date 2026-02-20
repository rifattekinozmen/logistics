<?php

namespace App\BusinessPartner\Models;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessPartner extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'partner_number',
        'partner_type',
        'name',
        'short_name',
        'tax_number',
        'tax_office',
        'phone',
        'email',
        'address',
        'currency',
        'payment_terms',
        'credit_limit',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
            'credit_limit' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function isVendor(): bool
    {
        return in_array($this->partner_type, ['vendor', 'carrier', 'both'], true);
    }

    public function isCustomer(): bool
    {
        return in_array($this->partner_type, ['customer', 'both'], true);
    }
}
