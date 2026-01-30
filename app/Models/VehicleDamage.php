<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleDamage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'inspection_id',
        'vehicle_id',
        'damage_date',
        'damage_location',
        'damage_type',
        'damage_size',
        'severity',
        'description',
        'digital_drawing_data',
        'status',
        'photos',
        'created_by',
        'approved_by',
        'approved_at',
        'repaired_at',
    ];

    protected function casts(): array
    {
        return [
            'damage_date' => 'date',
            'digital_drawing_data' => 'array',
            'photos' => 'array',
            'approved_at' => 'datetime',
            'repaired_at' => 'datetime',
        ];
    }

    /**
     * Get the inspection that owns the damage.
     */
    public function inspection(): BelongsTo
    {
        return $this->belongsTo(VehicleInspection::class);
    }

    /**
     * Get the vehicle that owns the damage.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the user who created the damage.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved the damage.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
