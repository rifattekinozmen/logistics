<?php

namespace App\Core\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    /**
     * Log an action.
     */
    public function log(string $action, string $tableName, int $recordId, ?array $oldData = null, ?array $newData = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'old_data' => $oldData ? json_encode($oldData) : null,
            'new_data' => $newData ? json_encode($newData) : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log model creation.
     */
    public function logCreate(Model $model): AuditLog
    {
        return $this->log(
            'created',
            $model->getTable(),
            $model->id,
            null,
            $model->getAttributes()
        );
    }

    /**
     * Log model update.
     */
    public function logUpdate(Model $model, array $oldAttributes): AuditLog
    {
        return $this->log(
            'updated',
            $model->getTable(),
            $model->id,
            $oldAttributes,
            $model->getAttributes()
        );
    }

    /**
     * Log model deletion.
     */
    public function logDelete(Model $model): AuditLog
    {
        return $this->log(
            'deleted',
            $model->getTable(),
            $model->id,
            $model->getAttributes(),
            null
        );
    }
}
