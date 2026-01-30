<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleInspection extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'inspection_date',
        'inspector_name',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'inspection_date' => 'date',
        ];
    }

    /**
     * Get the vehicle that owns the inspection.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the user who created the inspection.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the damages for the inspection.
     */
    public function damages(): HasMany
    {
        return $this->hasMany(VehicleDamage::class, 'inspection_id');
    }
}
