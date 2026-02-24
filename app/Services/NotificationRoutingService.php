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
        array $meta = []
    ): void {
        User::query()
            ->permission($permission)
            ->where('is_active', true)
            ->cursor()
            ->each(fn (User $user) => $user->notify(new WorkflowStageNotification($title, $message, $type, $meta)));
    }
}

