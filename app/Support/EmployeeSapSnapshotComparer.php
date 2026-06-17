<?php

namespace App\Support;

use App\Models\Employee;
use App\Models\EmployeeHistory;
use App\Models\EmployeeSapSnapshotDifference;
use App\Models\EmployeeSapSnapshotDifferenceItem;
use App\Models\EmployeeSapSnapshotRow;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeSapSnapshotComparer
{
    public function compareRow(EmployeeSapSnapshotRow $row): void
    {
        $employee = Employee::query()
            ->with(['position', 'employmentStatus'])
            ->where('nik_sap', $row->nik_sap)
            ->first();

        $row->forceFill(['employee_id' => $employee?->getKey()])->saveQuietly();

        if (! $employee) {
            return;
        }

        $items = [];

        foreach (EmployeeSapFieldMap::trackedFields() as $field => $label) {
            $sapValue = EmployeeSapFieldMap::sapValue($row, $field);
            $localValue = EmployeeSapFieldMap::employeeValue($employee, $field);

            if (EmployeeSapFieldMap::normalize($sapValue) === EmployeeSapFieldMap::normalize($localValue)) {
                $this->markPreviousItemsRecorded($employee, $field, $localValue, $row);

                continue;
            }

            $items[$field] = [
                'field_name' => $field,
                'field_label' => $label,
                'sap_value' => EmployeeSapFieldMap::display($sapValue),
                'local_value' => EmployeeSapFieldMap::display($localValue),
                'local_changed_at' => $this->latestLocalChangeAt($employee, $field),
            ];
        }

        DB::transaction(function () use ($row, $employee, $items): void {
            $difference = EmployeeSapSnapshotDifference::query()->updateOrCreate(
                [
                    'employee_sap_snapshot_id' => $row->employee_sap_snapshot_id,
                    'nik_sap' => $row->nik_sap,
                ],
                [
                    'employee_id' => $employee->getKey(),
                    'employee_name' => $employee->full_name,
                    'difference_count' => count($items),
                    'detected_at' => now(),
                ],
            );

            $difference->items()
                ->whereNotIn('field_name', array_keys($items))
                ->delete();

            foreach ($items as $field => $item) {
                $difference->items()->updateOrCreate(
                    ['field_name' => $field],
                    $item,
                );
            }

            if ($items === []) {
                $difference->delete();
            }
        });
    }

    private function latestLocalChangeAt(Employee $employee, string $sapField): ?Carbon
    {
        $historyField = match ($sapField) {
            'position' => 'position_id',
            'employment_status' => 'employment_status_id',
            default => $sapField,
        };

        return EmployeeHistory::query()
            ->where('employee_id', $employee->getKey())
            ->where('event', 'updated')
            ->latest()
            ->get()
            ->first(fn (EmployeeHistory $history): bool => array_key_exists($historyField, $history->new_values ?? []))
            ?->created_at;
    }

    private function markPreviousItemsRecorded(Employee $employee, string $field, mixed $localValue, EmployeeSapSnapshotRow $row): void
    {
        EmployeeSapSnapshotDifferenceItem::query()
            ->where('field_name', $field)
            ->where('local_value', EmployeeSapFieldMap::display($localValue))
            ->where('is_recorded_in_sap', false)
            ->whereHas('difference', function ($query) use ($employee, $row): void {
                $query
                    ->where('employee_id', $employee->getKey())
                    ->where('employee_sap_snapshot_id', '<>', $row->employee_sap_snapshot_id);
            })
            ->update([
                'is_recorded_in_sap' => true,
                'recorded_in_sap_at' => now(),
                'remark' => DB::raw("COALESCE(remark, 'Terdeteksi sama dengan SAP pada snapshot berikutnya.')"),
            ]);
    }
}
