<?php

namespace App\Services\EmployeeSap;

use App\Models\Employee;
use App\Models\EmployeeHistory;
use App\Models\EmployeeSapSnapshot;
use App\Models\EmployeeSapSnapshotRow;
use Illuminate\Support\Facades\DB;

class CompareEmployeeSapSnapshotService
{
    public function compare(EmployeeSapSnapshot $snapshot): void
    {
        DB::transaction(function () use ($snapshot): void {
            $snapshot->differences()->delete();

            $snapshot->rows()
                ->with('snapshot')
                ->orderBy('id')
                ->each(fn (EmployeeSapSnapshotRow $row) => $this->compareRow($snapshot, $row));
        });
    }

    private function compareRow(EmployeeSapSnapshot $snapshot, EmployeeSapSnapshotRow $row): void
    {
        $employee = Employee::query()
            ->with(['position', 'employmentStatus'])
            ->where('nik_sap', $row->nik_sap)
            ->first();

        if (! $employee) {
            return;
        }

        $items = [];

        foreach (EmployeeSapFieldMap::comparableFields() as $fieldName => $config) {
            $sapValue = $row->{$config['sap']};
            $localValue = $config['local']($employee);

            if (EmployeeSapFieldMap::normalize($sapValue) === EmployeeSapFieldMap::normalize($localValue)) {
                continue;
            }

            $items[] = [
                'field_name' => $fieldName,
                'field_label' => $config['label'],
                'sap_value' => EmployeeSapFieldMap::display($sapValue),
                'local_value' => EmployeeSapFieldMap::display($localValue),
                'local_changed_at' => $this->latestLocalChangeAt($employee, $config['history'] ?? $fieldName),
                'is_recorded_in_sap' => false,
            ];
        }

        if ($items === []) {
            return;
        }

        $difference = $snapshot->differences()->create([
            'employee_id' => $employee->getKey(),
            'nik_sap' => $row->nik_sap,
            'name' => $employee->full_name ?: $row->name,
            'difference_count' => count($items),
            'detected_at' => $snapshot->imported_at ?? now(),
        ]);

        $difference->items()->createMany($items);
    }

    private function latestLocalChangeAt(Employee $employee, string $field): mixed
    {
        return EmployeeHistory::query()
            ->where('employee_id', $employee->getKey())
            ->whereJsonContains('changed_fields', $field)
            ->latest()
            ->value('created_at');
    }
}
