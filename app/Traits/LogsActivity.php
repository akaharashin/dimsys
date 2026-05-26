<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    protected function logActivity(
        string  $action,
        string  $module,
        mixed   $record  = null,
        ?array  $before  = null,
        ?array  $after   = null,
        ?string $label   = null
    ): void {
        ActivityLog::log(
            action:       $action,
            module:       $module,
            record_id:    $record?->id,
            record_label: $label ?? ($record ? class_basename($record) . ' #' . $record->id : null),
            before:       $before,
            after:        $after,
        );
    }
}
