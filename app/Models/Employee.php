<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'branch_id',
        'position_id',
        'employee_number',
        'first_name',
        'last_name',
        'phone',
        'email',
        'salary',
        'hire_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'salary' => 'decimal:2',
            'hire_date' => 'date',
            'status' => 'integer',
        ];
    }

    /**
     * Get the user associated with the employee.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the branch that owns the employee.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the position that owns the employee.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get the attendance records for the employee.
     */
    public function attendance(): HasMany
    {
        return $this->hasMany(PersonnelAttendance::class);
    }

    /**
     * Get the documents for the employee.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'documentable_id')
            ->where('documentable_type', self::class);
    }

    /**
     * Get the leaves for the employee.
     */
    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    /**
     * Get the advances for the employee.
     */
    public function advances(): HasMany
    {
        return $this->hasMany(Advance::class);
    }

    /**
     * Get the payrolls for the employee.
     */
    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }
}
