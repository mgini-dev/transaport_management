<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\User;
use App\Support\NmisDataScope;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository
{
    public function listForIndex(User $user, int $skip, int $take, ?string $status = null, ?string $search = null): Collection
    {
        return NmisDataScope::ownOrAll(
            query: Order::query(),
            user: $user,
            ownerColumn: 'created_by',
            viewAllPermission: 'orders.view_all'
        )
            ->with(['trip:id,trip_number', 'customer:id,name'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($search, fn ($query) => $query->where(function ($inner) use ($search) {
                $inner->where('order_number', 'like', '%'.$search.'%')
                    ->orWhere('cargo_type', 'like', '%'.$search.'%');
            }))
            ->latest()
            ->skip($skip)
            ->take($take)
            ->get();
    }
}
