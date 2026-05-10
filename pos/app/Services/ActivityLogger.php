<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogger
{
    public static function log($action, $description, $storeId = null, $userId = null)
    {
        $user = auth()->user();

        if (!$user && !$userId) {
            return null;
        }

        return ActivityLog::create([
            'store_id' => $storeId ?? $user?->store_id,
            'user_id' => $userId ?? $user->id,
            'action' => $action,
            'description' => $description,
        ]);
    }
}
