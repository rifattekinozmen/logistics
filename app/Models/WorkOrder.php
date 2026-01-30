<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'work_order_number',
        'vehicle_id',
        'work_order_type',
        'priority',
        'description',
        'estimated_duration',
        'estimated_cost',
        'actual_duration',
        'actual_cost',
        'status',
        'service_provider_id',
        'assigned_technician_id',
        'started_at',
        'completed_at',
        'approved_by',
        'approved_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'estimated_duration' => 'integer',
            'estimated_cost' => 'decimal:2',
            'actual_duration' => 'integer',
            'actual_cost' => 'decimal:2',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * Get the vehicle that owns the work order.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the service provider for the work order.
     */
    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    /**
     * Get the technician assigned to the work order.
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_technician_id');
    }

    /**
     * Get the user who approved the work order.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who created the work order.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
