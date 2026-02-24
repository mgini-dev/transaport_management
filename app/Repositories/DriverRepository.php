<?php

namespace App\Repositories;

use App\Models\Driver;
use App\Models\User;
use App\Support\NmisDataScope;
use Illuminate\Database\Eloquent\Collection;

class DriverRepository
{
    public function listForIndex(User $user, int $skip, int $take, ?string $search = null, ?string $active = null): Collection
    {
        return NmisDataScope::ownOrAll(
            query: Driver::query(),
            user: $user,
            ownerColumn: 'created_by',
            viewAllPermission: 'drivers.view_all'
        )
            ->with('fleet:id,fleet_code,plate_number')
            ->when($search, fn ($query) => $query->where(function ($inner) use ($search) {
                $inner->where('name', 'like', '%'.$search.'%')
                    ->orWhere('license_number', 'like', '%'.$search.'%')
                    ->orWhere('mobile_number', 'like', '%'.$search.'%');
            }))
            ->when($active !== null && $active !== '', fn ($query) => $query->where('is_active', (bool) $active))
            ->latest()
            ->skip($skip)
            ->take($take)
            ->get();
    }
}
