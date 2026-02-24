<?php

namespace App\Repositories;

use App\Models\Driver;
use App\Models\User;
use App\Support\NmisDataScope;
use Illuminate\Database\Eloquent\Collection;

class DriverRepository
{
    public function listForIndex(User $user, int $skip, int $take): Collection
    {
        return NmisDataScope::ownOrAll(
            query: Driver::query(),
            user: $user,
            ownerColumn: 'created_by',
            viewAllPermission: 'drivers.view_all'
        )
            ->with('fleet:id,fleet_code,plate_number')
            ->latest()
            ->skip($skip)
            ->take($take)
            ->get();
    }
}
