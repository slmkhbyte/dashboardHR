<?php

namespace Tests\Feature;

use App\Filament\Widgets\EmployeeSapWorkUnitComparison;
use App\Models\Employee;
use App\Models\EmployeeSapSnapshot;
use App\Models\EmployeeSapSnapshotRow;
use App\Models\EmploymentStatus;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeSapWorkUnitComparisonTest extends TestCase
{
    use RefreshDatabase;

    public function test_comparison_counts_afdeling_rows_for_target_positions_and_statuses(): void
    {
        $karpelTetap = EmploymentStatus::query()->create(['name' => 'Karpel-Tetap', 'color' => 'success', 'is_active' => true]);
        $ktng = EmploymentStatus::query()->create(['name' => 'KTNG', 'color' => 'warning', 'is_active' => true]);
        $pkwt = EmploymentStatus::query()->create(['name' => 'PKWT', 'color' => 'info', 'is_active' => true]);
        $tetap = EmploymentStatus::query()->create(['name' => 'Tetap', 'color' => 'gray', 'is_active' => true]);

        $pemanen = Position::query()->create(['name' => 'pemanen', 'is_active' => true]);
        $pemeliharaan = Position::query()->create(['name' => 'PEMELIHARAAN', 'is_active' => true]);
        $mandor = Position::query()->create(['name' => 'MANDOR', 'is_active' => true]);

        $this->createEmployee('13000001', $pemanen, $karpelTetap, 'AFDELING I');
        $this->createEmployee('13000002', $pemanen, $ktng, 'afdeling i');
        $this->createEmployee('13000003', $pemeliharaan, $pkwt, 'AFDELING I');
        $this->createEmployee('13000004', $pemanen, $tetap, 'AFDELING I');
        $this->createEmployee('13000005', $mandor, $karpelTetap, 'AFDELING I');
        $this->createEmployee('13000006', $pemanen, $karpelTetap, 'KANTOR');
        $this->createEmployee('13000007', $pemanen, $karpelTetap, 'AFDELING I', false);

        $oldSnapshot = EmployeeSapSnapshot::query()->create([
            'period_month' => 5,
            'period_year' => 2026,
            'imported_at' => now()->subMonth(),
        ]);

        EmployeeSapSnapshotRow::query()->create([
            'employee_sap_snapshot_id' => $oldSnapshot->getKey(),
            'nik_sap' => '23000001',
            'position' => 'PEMANEN',
            'employment_status' => 'Karpel-Tetap',
            'work_unit' => 'AFDELING I',
            'is_active' => true,
        ]);

        $latestSnapshot = EmployeeSapSnapshot::query()->create([
            'period_month' => 6,
            'period_year' => 2026,
            'imported_at' => now(),
        ]);

        EmployeeSapSnapshotRow::query()->create([
            'employee_sap_snapshot_id' => $latestSnapshot->getKey(),
            'nik_sap' => '23000002',
            'position' => 'pemanen',
            'employment_status' => 'Karpel-Tetap',
            'work_unit' => 'AFDELING I',
            'is_active' => true,
        ]);
        EmployeeSapSnapshotRow::query()->create([
            'employee_sap_snapshot_id' => $latestSnapshot->getKey(),
            'nik_sap' => '23000003',
            'position' => 'PEMELIHARAAN',
            'employment_status' => 'PKWT',
            'work_unit' => 'AFDELING I',
            'is_active' => null,
        ]);
        EmployeeSapSnapshotRow::query()->create([
            'employee_sap_snapshot_id' => $latestSnapshot->getKey(),
            'nik_sap' => '23000004',
            'position' => 'PEMANEN',
            'employment_status' => 'PKWT',
            'work_unit' => 'AFDELING I',
            'is_active' => false,
        ]);

        $widget = app(EmployeeSapWorkUnitComparison::class);
        $rows = $widget->getComparisonRows();

        $this->assertCount(1, $rows);
        $this->assertSame('AFDELING I', $rows[0]['work_unit']);

        $this->assertSame([
            'karpel_tetap' => 1,
            'ktng' => 1,
            'pkwt' => 0,
            'total' => 2,
        ], $rows[0]['positions']['PEMANEN']['local']);
        $this->assertSame([
            'karpel_tetap' => 1,
            'ktng' => 0,
            'pkwt' => 0,
            'total' => 1,
        ], $rows[0]['positions']['PEMANEN']['sap']);
        $this->assertSame([
            'karpel_tetap' => 0,
            'ktng' => 0,
            'pkwt' => 1,
            'total' => 1,
        ], $rows[0]['positions']['PEMELIHARAAN']['local']);
        $this->assertSame([
            'karpel_tetap' => 0,
            'ktng' => 0,
            'pkwt' => 1,
            'total' => 1,
        ], $rows[0]['positions']['PEMELIHARAAN']['sap']);
    }

    private function createEmployee(
        string $nikSap,
        Position $position,
        EmploymentStatus $employmentStatus,
        string $workUnit,
        bool $isActive = true,
    ): Employee {
        return Employee::query()->create([
            'nik_sap' => $nikSap,
            'full_name' => 'Employee ' . $nikSap,
            'hire_date' => now()->startOfMonth(),
            'position_id' => $position->getKey(),
            'employment_status_id' => $employmentStatus->getKey(),
            'work_unit' => $workUnit,
            'is_active' => $isActive,
        ]);
    }
}
