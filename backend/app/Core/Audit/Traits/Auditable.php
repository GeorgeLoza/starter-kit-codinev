<?php

declare(strict_types=1);

namespace App\Core\Audit\Traits;

use App\Core\Audit\Actions\RecordAuditAction;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            app(RecordAuditAction::class)->execute(
                action: 'created',
                model: $model,
                newValues: $model->auditAttributes(),
            );
        });

        static::updated(function ($model) {
            $changes = collect($model->getChanges())
                ->except($model->auditExclude())
                ->toArray();

            if (empty($changes)) {
                return;
            }

            $old = collect($changes)
                ->keys()
                ->mapWithKeys(fn ($key) => [$key => $model->getOriginal($key)])
                ->toArray();

            app(RecordAuditAction::class)->execute(
                action: 'updated',
                model: $model,
                oldValues: $old,
                newValues: $changes,
            );
        });

        static::deleted(function ($model) {
            app(RecordAuditAction::class)->execute(
                action: 'deleted',
                model: $model,
                oldValues: $model->auditAttributes(),
            );
        });
    }

    protected function auditAttributes(): array
    {
        return collect($this->getAttributes())
            ->except($this->auditExclude())
            ->toArray();
    }

    protected function auditExclude(): array
    {
        return property_exists($this, 'auditExclude') ? $this->auditExclude : [];
    }
}