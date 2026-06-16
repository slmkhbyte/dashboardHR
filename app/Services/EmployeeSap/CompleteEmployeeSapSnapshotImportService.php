<?php

namespace App\Services\EmployeeSap;

use App\Models\EmployeeSapSnapshot;

class CompleteEmployeeSapSnapshotImportService
{
    public function complete(EmployeeSapSnapshot $snapshot): void
    {
        $snapshot->forceFill([
            'imported_at' => $snapshot->imported_at ?? now(),
            'total_rows' => $snapshot->rows()->count(),
        ])->save();

        app(CompareEmployeeSapSnapshotService::class)->compare($snapshot);
        app(AutoReconcileEmployeeSapService::class)->reconcile($snapshot);
    }
}
