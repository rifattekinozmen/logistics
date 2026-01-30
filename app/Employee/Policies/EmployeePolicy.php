<?php

namespace App\Employee\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    /**
     * Determine if the user can view any employees.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('operation');
    }

    /**
     * Determine if the user can view the employee.
     */
    public function view(User $user, Employee $employee): bool
    {
        return $user->hasRole('admin') || $user->hasRole('operation') || $user->id === $employee->user_id;
    }

    /**
     * Determine if the user can create employees.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine if the user can update the employee.
     */
    public function update(User $user, Employee $employee): bool
    {
        return $user->hasRole('admin') || $user->hasRole('operation');
    }

    /**
     * Determine if the user can delete the employee.
     */
    public function delete(User $user, Employee $employee): bool
    {
        return $user->hasRole('admin');
    }
}
