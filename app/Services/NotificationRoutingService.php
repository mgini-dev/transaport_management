<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\WorkflowStageNotification;

class NotificationRoutingService
{
    public function notifyPermission(
        string $permission,
        string $title,
        string $message,
        string $type,
        array $meta = [],
        ?callable $filter = null,
        ?int $excludeUserId = null,
    ): void {
        $query = User::query()
            ->permission($permission)
            ->where('is_active', true);

        if ($excludeUserId) {
            $query->whereKeyNot($excludeUserId);
        }

        $query
            ->cursor()
            ->filter(fn (User $user) => $filter ? (bool) $filter($user) : true)
            ->each(fn (User $user) => $user->notify(new WorkflowStageNotification($title, $message, $type, $meta)));
    }
}
