<?php

namespace App\Policies;

use App\Models\Trip;
use App\Models\User;

class TripPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('trips.view');
    }

    public function view(User $user, Trip $trip): bool
    {
        return $user->can('trips.view') && ($user->can('trips.view_all') || $trip->created_by === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->can('trips.create');
    }

    public function close(User $user, Trip $trip): bool
    {
        if (! $user->can('trips.close')) {
            return false;
        }

        return $user->can('trips.view_all') || $trip->created_by === $user->id;
    }
}
