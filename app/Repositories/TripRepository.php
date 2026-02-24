<?php

namespace App\Repositories;

use App\Models\Trip;
use App\Models\User;
use App\Support\NmisDataScope;
use Illuminate\Database\Eloquent\Collection;

class TripRepository
{
    public function listForIndex(User $user, int $skip, int $take): Collection
    {
        return NmisDataScope::ownOrAll(
            query: Trip::query(),
            user: $user,
            ownerColumn: 'created_by',
            viewAllPermission: 'trips.view_all'
        )
            ->latest()
            ->skip($skip)
            ->take($take)
            ->get();
    }
}
