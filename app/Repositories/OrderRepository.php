<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\User;
use App\Support\NmisDataScope;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository
{
    public function listForIndex(User $user, int $skip, int $take): Collection
    {
        return NmisDataScope::ownOrAll(
            query: Order::query(),
            user: $user,
            ownerColumn: 'created_by',
            viewAllPermission: 'orders.view_all'
        )
            ->with(['trip:id,trip_number', 'customer:id,name'])
            ->latest()
            ->skip($skip)
            ->take($take)
            ->get();
    }
}
