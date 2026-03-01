<?php

namespace App\Services\Monitoring;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    public static function log(string $event, Model $model, ?array $before = null, ?array $after = null): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => $event,
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'before_state' => $before,
            'after_state' => $after,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    public static function onlyDirtyAttributes(Model $model): array
    {
        $dirtyKeys = array_keys($model->getChanges());
        $before = [];
        $after = [];

        foreach ($dirtyKeys as $key) {
            if (in_array($key, ['updated_at', 'deleted_at'], true)) {
                continue;
            }

            $before[$key] = $model->getOriginal($key);
            $after[$key] = $model->getAttribute($key);
        }

        return ['before' => $before, 'after' => $after];
    }
}
