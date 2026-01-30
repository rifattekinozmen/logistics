<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'tax_number',
        'phone',
        'email',
        'address',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
        ];
    }

    /**
     * Get the orders for the customer.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the payments for the customer.
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'related');
    }

    /**
     * Get the favorite addresses for the customer.
     */
    public function favoriteAddresses(): HasMany
    {
        return $this->hasMany(FavoriteAddress::class);
    }

    /**
     * Get the order templates for the customer.
     */
    public function orderTemplates(): HasMany
    {
        return $this->hasMany(OrderTemplate::class);
    }
}
