<?php

namespace App\Services\EmployeeSap;

use App\Models\Employee;
use App\Models\EmployeeSapSnapshot;
use App\Models\EmployeeSapSnapshotDifferenceItem;
use Illuminate\Support\Facades\DB;

class AutoReconcileEmployeeSapService
{
    public function reconcile(EmployeeSapSnapshot $snapshot): void
    {
        $rowsByNik = $snapshot->rows()->get()->keyBy('nik_sap');

        DB::transaction(function () use ($snapshot, $rowsByNik): void {
            EmployeeSapSnapshotDifferenceItem::query()
                ->where('is_recorded_in_sap', false)
                ->whereHas('difference', fn ($query) => $query->where('snapshot_id', '<', $snapshot->getKey()))
                ->with('difference.employee')
                ->each(function (EmployeeSapSnapshotDifferenceItem $item) use ($snapshot, $rowsByNik): void {
                    $employee = $item->difference?->employee;
                    $row = $rowsByNik->get($item->difference?->nik_sap);
                    $field = EmployeeSapFieldMap::comparableFields()[$item->field_name] ?? null;

                    if (! $employee instanceof Employee || ! $row || ! $field) {
                        return;
                    }

                    $sapValue = $row->{$field['sap']};
                    $localValue = $field['local']($employee);

                    if (EmployeeSapFieldMap::normalize($sapValue) !== EmployeeSapFieldMap::normalize($localValue)) {
                        return;
                    }

                    $item->forceFill([
                        'is_recorded_in_sap' => true,
                        'recorded_in_sap_at' => $snapshot->imported_at ?? now(),
                    ])->save();
                });
        });
    }
}
