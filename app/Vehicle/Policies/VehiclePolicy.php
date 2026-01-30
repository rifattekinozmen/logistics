<?php

namespace App\Vehicle\Policies;

use App\Models\User;
use App\Models\Vehicle;

class VehiclePolicy
{
    /**
     * Determine if the user can view any vehicles.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('operation');
    }

    /**
     * Determine if the user can view the vehicle.
     */
    public function view(User $user, Vehicle $vehicle): bool
    {
        return $user->hasRole('admin') || $user->hasRole('operation');
    }

    /**
     * Determine if the user can create vehicles.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('operation');
    }

    /**
     * Determine if the user can update the vehicle.
     */
    public function update(User $user, Vehicle $vehicle): bool
    {
        return $user->hasRole('admin') || $user->hasRole('operation');
    }

    /**
     * Determine if the user can delete the vehicle.
     */
    public function delete(User $user, Vehicle $vehicle): bool
    {
        return $user->hasRole('admin');
    }
}
