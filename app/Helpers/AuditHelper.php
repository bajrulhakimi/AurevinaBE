<?php

namespace App\Helpers;

use App\Models\AuditLog;

class AuditHelper
{
    public static function log($userId, $action, $tableName, $recordId, $oldData = null, $newData = null)
    {
        AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'old_data' => $oldData,
            'new_data' => $newData,
        ]);
    }
}
