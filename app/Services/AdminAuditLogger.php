<?php

namespace App\Services;

use App\Models\AdminAuditLog;
use Illuminate\Database\Eloquent\Model;

class AdminAuditLogger
{
    public function record(string $action, ?Model $record = null, array $metadata = []): void
    {
        AdminAuditLog::create([
            'admin_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => $record?->getMorphClass(),
            'auditable_id' => $record?->getKey(),
            'metadata' => $metadata,
        ]);
    }
}
