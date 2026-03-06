<?php

namespace App\Core\Audit;

use App\Models\Core\AuditLog;
use Illuminate\Http\Request;

class AuditLogger
{
    public function log(string $action, mixed $entity = null, array $meta = [], ?Request $request = null): void
    {
        $request ??= request();

        AuditLog::query()->create([
            'actor_user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => is_object($entity) ? $entity::class : null,
            'entity_id' => is_object($entity) && method_exists($entity, 'getKey') ? $entity->getKey() : null,
            'meta' => $meta,
            'ip_hash' => $request ? hash('sha256', (string) $request->ip()) : null,
            'user_agent' => $request?->userAgent(),
            'created_at' => now(),
        ]);
    }
}
