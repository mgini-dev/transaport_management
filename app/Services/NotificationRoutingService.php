<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\WorkflowStageNotification;
use Spatie\Permission\Models\Role;

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
        ?string $requiredAction = null,
        ?string $actionUrl = null,
    ): void {
        $query = User::query()
            ->with('roles:id,name')
            ->permission($permission)
            ->where('is_active', true);

        if ($excludeUserId) {
            $query->whereKeyNot($excludeUserId);
        }

        $requiredRoles = Role::query()
            ->permission($permission)
            ->pluck('name')
            ->values()
            ->all();

        $query
            ->cursor()
            ->filter(fn (User $user) => $filter ? (bool) $filter($user) : true)
            ->each(function (User $user) use (
                $title,
                $message,
                $type,
                $meta,
                $permission,
                $requiredRoles,
                $requiredAction,
                $actionUrl
            ): void {
                $notificationMeta = [
                    ...$meta,
                    'required_permission' => $permission,
                    'required_roles' => $requiredRoles,
                    'recipient_roles' => $user->getRoleNames()->values()->all(),
                    'required_action' => $requiredAction ?: 'Please complete the next workflow action.',
                ];

                if ($actionUrl) {
                    $notificationMeta['action_url'] = $actionUrl;
                }

                $user->notify(new WorkflowStageNotification($title, $message, $type, $notificationMeta));
            });
    }
}
