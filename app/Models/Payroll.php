<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'payroll_number',
        'period_start',
        'period_end',
        'base_salary',
        'overtime_amount',
        'bonus',
        'deduction',
        'tax',
        'social_security',
        'net_salary',
        'status',
        'payment_date',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'base_salary' => 'decimal:2',
            'overtime_amount' => 'decimal:2',
            'bonus' => 'decimal:2',
            'deduction' => 'decimal:2',
            'tax' => 'decimal:2',
            'social_security' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    /**
     * Get the employee that owns the payroll.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who created the payroll.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
