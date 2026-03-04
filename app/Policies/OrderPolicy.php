<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('orders.view');
    }

    public function view(User $user, Order $order): bool
    {
        return $user->can('orders.view') && ($user->can('orders.view_all') || $order->created_by === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->can('orders.create');
    }

    public function updateStatus(User $user, Order $order): bool
    {
        if ($order->trip?->status === 'closed') {
            return false;
        }

        if (! $user->can('orders.status.update')) {
            return false;
        }

        return $user->can('orders.view_all') || $order->created_by === $user->id;
    }

    public function manageLegs(User $user, Order $order): bool
    {
        if ($order->trip?->status === 'closed') {
            return false;
        }

        if (! $user->can('fleet.assign')) {
            return false;
        }

        return $user->can('orders.view_all') || $order->created_by === $user->id || $user->can('fleet.view_all');
    }

    public function viewDistance(User $user, Order $order): bool
    {
        if ($order->trip?->status === 'closed') {
            return false;
        }

        if (! $user->can('orders.view_distance')) {
            return false;
        }

        return $user->can('orders.view_all') || $order->created_by === $user->id || $user->can('fleet.view_all');
    }

    public function completeTransportation(User $user, Order $order): bool
    {
        if ($order->trip?->status === 'closed') {
            return false;
        }

        if ($order->status !== 'transportation') {
            return false;
        }

        return $order->created_by === $user->id
            || $user->can('fuel.create')
            || $user->can('fleet.assign');
    }
}
