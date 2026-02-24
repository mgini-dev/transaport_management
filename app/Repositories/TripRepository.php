<?php

namespace App\Repositories;

use App\Models\Trip;
use App\Models\User;
use App\Support\NmisDataScope;
use Illuminate\Database\Eloquent\Collection;

class TripRepository
{
    public function listForIndex(User $user, int $skip, int $take, ?string $status = null, ?string $search = null): Collection
    {
        return NmisDataScope::ownOrAll(
            query: Trip::query(),
            user: $user,
            ownerColumn: 'created_by',
            viewAllPermission: 'trips.view_all'
        )
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($search, fn ($query) => $query->where('trip_number', 'like', '%'.$search.'%'))
            ->latest()
            ->skip($skip)
            ->take($take)
            ->get();
    }
}
