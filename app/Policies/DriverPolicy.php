<?php

namespace App\Policies;

use App\Models\Driver;
use App\Models\User;

class DriverPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('drivers.view');
    }

    public function view(User $user, Driver $driver): bool
    {
        return $user->can('drivers.view') && ($user->can('drivers.view_all') || $driver->created_by === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->can('drivers.create');
    }

    public function update(User $user, Driver $driver): bool
    {
        if (! $user->can('drivers.update')) {
            return false;
        }

        return $user->can('drivers.view_all') || $driver->created_by === $user->id;
    }

    public function delete(User $user, Driver $driver): bool
    {
        if (! $user->can('drivers.delete')) {
            return false;
        }

        return $user->can('drivers.view_all') || $driver->created_by === $user->id;
    }
}
