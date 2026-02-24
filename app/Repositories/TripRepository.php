<?php

namespace App\Repositories;

use App\Models\Trip;
use App\Models\User;
use App\Support\NmisDataScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class TripRepository
{
    public function listForIndex(User $user, int $skip, int $take, ?string $status = null, ?string $search = null): Collection
    {
        return $this->scopedQuery($user)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($search, fn ($query) => $query->where('trip_number', 'like', '%'.$search.'%'))
            ->latest()
            ->skip($skip)
            ->take($take)
            ->get();
    }

    public function countForIndex(User $user, ?string $status = null, ?string $search = null): int
    {
        return $this->scopedQuery($user)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($search, fn ($query) => $query->where('trip_number', 'like', '%'.$search.'%'))
            ->count();
    }

    /**
     * @return array{total: int, open: int, closed: int}
     */
    public function statsForUser(User $user): array
    {
        $query = $this->scopedQuery($user);

        $total = (clone $query)->count();
        $open = (clone $query)->where('status', 'open')->count();
        $closed = (clone $query)->where('status', 'closed')->count();

        return [
            'total' => $total,
            'open' => $open,
            'closed' => $closed,
        ];
    }

    private function scopedQuery(User $user): Builder
    {
        return NmisDataScope::ownOrAll(
            query: Trip::query(),
            user: $user,
            ownerColumn: 'created_by',
            viewAllPermission: 'trips.view_all'
        );
    }
}
