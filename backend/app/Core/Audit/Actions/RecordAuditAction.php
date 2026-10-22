<?php

declare(strict_types=1);

namespace App\Core\Audit\Actions;

use App\Core\Audit\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

final class RecordAuditAction
{
    public function execute(
        string $action,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null,
    ): AuditLog {
        $request = request();

        return AuditLog::create([
            'user_id'    => $userId ?? $request?->user()?->id,
            'action'     => $action,
            'model_type' => $model?->getMorphClass(),
            'model_id'   => $model?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}