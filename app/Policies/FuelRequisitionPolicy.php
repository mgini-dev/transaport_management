<?php

namespace App\Policies;

use App\Models\FuelRequisition;
use App\Models\User;

class FuelRequisitionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('fuel.view');
    }

    public function view(User $user, FuelRequisition $requisition): bool
    {
        return $user->can('fuel.view') && ($user->can('fuel.view_all') || $requisition->requested_by === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->can('fuel.create');
    }

    public function supervisorDecision(User $user, FuelRequisition $requisition): bool
    {
        return $user->can('fuel.approve.supervisor');
    }

    public function accountantDecision(User $user, FuelRequisition $requisition): bool
    {
        return $user->can('fuel.approve.accounting');
    }
}
