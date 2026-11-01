<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    public function record(?User $actor, string $action, ?Model $model = null, array $old = [], array $new = []): void
    {
        AuditLog::create([
            'user_id' => $actor?->id,
            'action' => $action,
            'model_type' => $model ? class_basename($model) : null,
            'model_id' => $model?->getKey(),
            'old_values' => $old ?: null,
            'new_values' => $new ?: null,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 1000),
            'created_at' => now(),
        ]);
    }
}
