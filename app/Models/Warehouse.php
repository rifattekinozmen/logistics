<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'name',
        'address',
        'warehouse_type',
        'status',
        'manager_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
        ];
    }

    /**
     * Get the company that owns the warehouse.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the branch that owns the warehouse.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the manager of the warehouse.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the locations for the warehouse.
     */
    public function locations(): HasMany
    {
        return $this->hasMany(WarehouseLocation::class);
    }

    /**
     * Get the stocks for the warehouse.
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class);
    }
}
