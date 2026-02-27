<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'plate',
        'license_number',
        'brand',
        'series',
        'model',
        'year',
        'color',
        'mileage',
        'vehicle_type',
        'vehicle_subtype',
        'fuel_type',
        'transmission',
        'owner_type',
        'engine_number',
        'vin_number',
        'first_registration_date',
        'registration_sequence_no',
        'registration_date',
        'hgs_number',
        'hgs_bank',
        'capacity_kg',
        'capacity_m3',
        'net_weight_kg',
        'train_weight_kg',
        'trailer_max_weight_kg',
        'seat_count',
        'standing_passenger_count',
        'engine_displacement_cm3',
        'engine_power_kw',
        'usage_purpose',
        'type_approval_no',
        'status',
        'branch_id',
        'notes',
        'owner_id_tax_no',
        'owner_surname_trade_name',
        'owner_first_name',
        'owner_address',
        'rights_holders',
        'notary_sale_date',
        'notary_sale_no',
        'notary_name',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'mileage' => 'integer',
            'capacity_kg' => 'decimal:2',
            'capacity_m3' => 'decimal:2',
            'net_weight_kg' => 'decimal:2',
            'train_weight_kg' => 'decimal:2',
            'trailer_max_weight_kg' => 'decimal:2',
            'engine_power_kw' => 'decimal:2',
            'first_registration_date' => 'date',
            'registration_date' => 'date',
            'notary_sale_date' => 'date',
            'status' => 'integer',
        ];
    }

    /**
     * Get the branch that owns the vehicle.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the inspections for the vehicle.
     */
    public function inspections(): HasMany
    {
        return $this->hasMany(VehicleInspection::class);
    }

    /**
     * Get the damages for the vehicle.
     */
    public function damages(): HasMany
    {
        return $this->hasMany(VehicleDamage::class);
    }

    /**
     * Get the work orders for the vehicle.
     */
    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    /**
     * Get the documents for the vehicle.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'documentable_id')
            ->where('documentable_type', self::class);
    }

    /**
     * Get GPS position records (Faz 3 - real-time takip).
     */
    public function gpsPositions(): HasMany
    {
        return $this->hasMany(VehicleGpsPosition::class);
    }
}
