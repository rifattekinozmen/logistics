<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Araç anlık GPS konumu (Faz 3 - real-time takip için minimal model).
 */
class VehicleGpsPosition extends Model
{
    protected $table = 'vehicle_gps_positions';

    protected $fillable = [
        'vehicle_id',
        'latitude',
        'longitude',
        'recorded_at',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'recorded_at' => 'datetime',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
