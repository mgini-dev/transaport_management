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
        if (! $user->can('orders.status.update')) {
            return false;
        }

        return $user->can('orders.view_all') || $order->created_by === $user->id;
    }

    public function manageLegs(User $user, Order $order): bool
    {
        if (! $user->can('fleet.assign')) {
            return false;
        }

        return $user->can('orders.view_all') || $order->created_by === $user->id || $user->can('fleet.view_all');
    }
}
