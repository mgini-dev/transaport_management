<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('hr.employees.view') || $user->can('hr.employees.manage');
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->can('hr.employees.view') || $user->can('hr.employees.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('hr.employees.manage');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->can('hr.employees.manage');
    }

    public function updateStatus(User $user, Employee $employee): bool
    {
        return $user->can('hr.employees.manage');
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->can('hr.employees.manage');
    }
}
