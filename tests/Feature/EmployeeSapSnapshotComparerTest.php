<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeSapSnapshot;
use App\Models\EmployeeSapSnapshotDifferenceItem;
use App\Models\EmployeeSapSnapshotRow;
use App\Models\EmploymentStatus;
use App\Models\Position;
use App\Support\EmployeeSapSnapshotComparer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeSapSnapshotComparerTest extends TestCase
{
    use RefreshDatabase;

    public function test_sap_snapshot_compare_stores_one_difference_row_with_field_level_items(): void
    {
        $localPosition = Position::query()->create(['name' => 'Supervisor HR', 'is_active' => true]);
        $status = EmploymentStatus::query()->create(['name' => 'Tetap', 'color' => 'info', 'is_active' => true]);

        $employee = Employee::query()->create([
            'nik_sap' => '13004844',
            'nik_karyawan' => '000.0194.0573.0337',
            'full_name' => 'Nama Karyawan',
            'hire_date' => now()->toDateString(),
            'position_id' => $localPosition->getKey(),
            'employment_status_id' => $status->getKey(),
            'employee_grade' => 'IB/13',
            'work_unit' => 'AFDELING II',
            'lvl_bod' => 6,
            'is_active' => true,
        ]);

        $snapshot = EmployeeSapSnapshot::query()->create([
            'period_month' => 6,
            'period_year' => 2026,
            'imported_at' => now(),
        ]);

        $row = EmployeeSapSnapshotRow::query()->create([
            'employee_sap_snapshot_id' => $snapshot->getKey(),
            'nik_sap' => $employee->nik_sap,
            'full_name' => $employee->full_name,
            'position' => 'Staff HR',
            'employment_status' => $status->name,
            'employee_grade' => $employee->employee_grade,
            'work_unit' => 'AFDELING I',
            'lvl_bod' => 6,
            'hire_date' => $employee->hire_date,
            'is_active' => true,
        ]);

        app(EmployeeSapSnapshotComparer::class)->compareRow($row);

        $difference = $snapshot->differences()->with('items')->sole();

        $this->assertSame('13004844', $difference->nik_sap);
        $this->assertSame(2, $difference->difference_count);
        $this->assertCount(2, $difference->items);
        $this->assertSame([
            'position',
            'work_unit',
        ], $difference->items->pluck('field_name')->sort()->values()->all());
    }

    public function test_matching_later_sap_snapshot_marks_older_difference_item_as_recorded(): void
    {
        $position = Position::query()->create(['name' => 'Supervisor HR', 'is_active' => true]);
        $status = EmploymentStatus::query()->create(['name' => 'Tetap', 'color' => 'info', 'is_active' => true]);

        $employee = Employee::query()->create([
            'nik_sap' => '13004844',
            'nik_karyawan' => '000.0194.0573.0337',
            'full_name' => 'Nama Karyawan',
            'hire_date' => now()->toDateString(),
            'position_id' => $position->getKey(),
            'employment_status_id' => $status->getKey(),
            'employee_grade' => 'IB/13',
            'work_unit' => 'AFDELING II',
            'lvl_bod' => 6,
            'is_active' => true,
        ]);

        $oldSnapshot = EmployeeSapSnapshot::query()->create([
            'period_month' => 5,
            'period_year' => 2026,
        ]);

        $oldDifference = $oldSnapshot->differences()->create([
            'employee_id' => $employee->getKey(),
            'nik_sap' => $employee->nik_sap,
            'employee_name' => $employee->full_name,
            'difference_count' => 1,
            'detected_at' => now(),
        ]);

        $oldDifference->items()->create([
            'field_name' => 'work_unit',
            'field_label' => 'Work Unit',
            'sap_value' => 'AFDELING I',
            'local_value' => 'AFDELING II',
        ]);

        $newSnapshot = EmployeeSapSnapshot::query()->create([
            'period_month' => 6,
            'period_year' => 2026,
        ]);

        $newRow = EmployeeSapSnapshotRow::query()->create([
            'employee_sap_snapshot_id' => $newSnapshot->getKey(),
            'nik_sap' => $employee->nik_sap,
            'full_name' => $employee->full_name,
            'position' => $position->name,
            'employment_status' => $status->name,
            'employee_grade' => $employee->employee_grade,
            'work_unit' => 'AFDELING II',
            'lvl_bod' => 6,
            'hire_date' => $employee->hire_date,
            'is_active' => true,
        ]);

        app(EmployeeSapSnapshotComparer::class)->compareRow($newRow);

        $this->assertTrue(EmployeeSapSnapshotDifferenceItem::query()->sole()->is_recorded_in_sap);
        $this->assertSame(0, $newSnapshot->differences()->count());
    }
}
