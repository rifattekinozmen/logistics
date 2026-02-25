<?php

namespace App\Employee\Services;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EmployeeService
{
    /**
     * Create a new employee.
     */
    public function create(array $data): Employee
    {
        if (! isset($data['employee_number'])) {
            $data['employee_number'] = $this->generateEmployeeNumber();
        }

        return Employee::create($data);
    }

    /**
     * Update an existing employee.
     */
    public function update(Employee $employee, array $data): Employee
    {
        $employee->update($data);

        return $employee->fresh();
    }

    /**
     * Get paginated employees.
     */
    public function getPaginated(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = Employee::query()->with(['branch', 'position', 'user']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (isset($filters['position_id'])) {
            $query->where('position_id', $filters['position_id']);
        }

        $sort = $filters['sort'] ?? null;
        $direction = (isset($filters['direction']) && $filters['direction'] === 'desc') ? 'desc' : 'asc';
        $sortableColumns = [
            'employee_number' => 'employee_number',
            'first_name' => 'first_name',
            'hire_date' => 'hire_date',
            'status' => 'status',
            'created_at' => 'created_at',
        ];
        if ($sort !== null && \array_key_exists($sort, $sortableColumns)) {
            $query->orderBy($sortableColumns[$sort], $direction);
        } else {
            $query->latest();
        }

        return $query->paginate($perPage);
    }

    /**
     * Filtrelenmiş personeli export için getir.
     */
    public function getForExport(array $filters = []): Collection
    {
        $query = Employee::query()->with(['branch', 'position', 'user']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (isset($filters['position_id'])) {
            $query->where('position_id', $filters['position_id']);
        }

        return $query->latest()->get();
    }

    /**
     * Generate unique employee number.
     */
    protected function generateEmployeeNumber(): string
    {
        do {
            $number = 'EMP-'.\strtoupper(\fake()->bothify('######'));
        } while (Employee::where('employee_number', $number)->exists());

        return $number;
    }
}
