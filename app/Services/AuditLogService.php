<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    public function record(
        string $action,
        ?Authenticatable $user = null,
        ?Model $loggable = null,
        array $context = [],
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): AuditLog {
        return AuditLog::query()->create([
            'user_id' => $user?->getAuthIdentifier(),
            'action' => $action,
            'loggable_type' => $loggable?->getMorphClass(),
            'loggable_id' => $loggable?->getKey(),
            'context' => $context,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }
}

