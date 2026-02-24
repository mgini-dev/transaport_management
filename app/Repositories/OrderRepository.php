<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\User;
use App\Support\NmisDataScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository
{
    public function listForIndex(User $user, int $skip, int $take, ?string $status = null, ?string $search = null): Collection
    {
        return $this->scopedQuery($user)
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

    public function countForIndex(User $user, ?string $status = null, ?string $search = null): int
    {
        return $this->scopedQuery($user)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($search, fn ($query) => $query->where(function ($inner) use ($search) {
                $inner->where('order_number', 'like', '%'.$search.'%')
                    ->orWhere('cargo_type', 'like', '%'.$search.'%');
            }))
            ->count();
    }

    /**
     * @return array{total: int, processing: int, completed: int, total_weight: float}
     */
    public function statsForUser(User $user): array
    {
        $query = $this->scopedQuery($user);

        $total = (clone $query)->count();
        $processing = (clone $query)->whereIn('status', ['processing', 'assigned'])->count();
        $completed = (clone $query)->where('status', 'completed')->count();
        $totalWeight = (float) ((clone $query)->sum('weight_tons') ?? 0);

        return [
            'total' => $total,
            'processing' => $processing,
            'completed' => $completed,
            'total_weight' => round($totalWeight, 2),
        ];
    }

    private function scopedQuery(User $user): Builder
    {
        return NmisDataScope::ownOrAll(
            query: Order::query(),
            user: $user,
            ownerColumn: 'created_by',
            viewAllPermission: 'orders.view_all'
        );
    }
}
