<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NmisDataScope
{
    /**
     * @param  Builder<Model>  $query
     */
    public static function ownOrAll(Builder $query, User $user, string $ownerColumn, string $viewAllPermission): Builder
    {
        if ($user->can($viewAllPermission) || $user->can('admin.dashboard.view_all')) {
            return $query;
        }

        return $query->where($ownerColumn, $user->id);
    }
}
