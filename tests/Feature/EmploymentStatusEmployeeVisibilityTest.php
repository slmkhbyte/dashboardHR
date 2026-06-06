<?php

namespace Tests\Feature;

use App\Filament\Resources\EmploymentStatuses\EmploymentStatusResource;
use App\Filament\Resources\EmploymentStatuses\RelationManagers\EmployeesRelationManager;
use App\Models\Employee;
use App\Models\EmploymentStatus;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmploymentStatusEmployeeVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_employment_status_resource_counts_only_active_employees_and_registers_employee_relation_manager(): void
    {
        $employmentStatus = EmploymentStatus::query()->create([
            'name' => 'Tetap',
            'color' => 'success',
            'is_active' => true,
        ]);

        $this->createEmployee($employmentStatus, ['nik_sap' => '13003001', 'is_active' => true]);
        $this->createEmployee($employmentStatus, ['nik_sap' => '13003002', 'is_active' => true]);
        $this->createEmployee($employmentStatus, ['nik_sap' => '13003003', 'is_active' => false]);

        $this->assertSame(
            2,
            EmploymentStatus::query()
                ->whereKey($employmentStatus->id)
                ->withCount(['employees' => fn ($query) => $query->active()])
                ->firstOrFail()
                ->employees_count,
        );

        $this->assertContains(EmployeesRelationManager::class, EmploymentStatusResource::getRelations());
    }

    public function test_employment_status_employee_relation_manager_tabs_split_active_and_all_employees(): void
    {
        $employmentStatus = EmploymentStatus::query()->create([
            'name' => 'Probation',
            'color' => 'info',
            'is_active' => true,
        ]);

        $activeEmployee = $this->createEmployee($employmentStatus, ['nik_sap' => '13004001', 'is_active' => true]);
        $inactiveEmployee = $this->createEmployee($employmentStatus, ['nik_sap' => '13004002', 'is_active' => false]);

        $manager = new class extends EmployeesRelationManager
        {
            public function ownerRecordForTest(EmploymentStatus $employmentStatus): void
            {
                $this->ownerRecord = $employmentStatus;
            }
        };

        $manager->ownerRecordForTest($employmentStatus);

        $tabs = $manager->getTabs();

        $this->assertSame(
            [$activeEmployee->id],
            $tabs['active']->modifyQuery(Employee::query()->where('employment_status_id', $employmentStatus->id))->pluck('id')->all(),
        );

        $allIds = $tabs['all']->modifyQuery(Employee::query()->where('employment_status_id', $employmentStatus->id))->pluck('id')->all();
        sort($allIds);

        $expectedIds = [$activeEmployee->id, $inactiveEmployee->id];
        sort($expectedIds);

        $this->assertSame($expectedIds, $allIds);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createEmployee(EmploymentStatus $employmentStatus, array $attributes = []): Employee
    {
        $position = Position::query()->create([
            'name' => 'Posisi ' . ($attributes['nik_sap'] ?? fake()->unique()->numerify('########')),
            'code' => 'POS' . fake()->unique()->numerify('###'),
            'is_active' => true,
        ]);

        return Employee::query()->create(array_merge([
            'nik_sap' => fake()->unique()->numerify('########'),
            'nik_karyawan' => fake()->unique()->numerify('################'),
            'full_name' => fake()->name(),
            'hire_date' => now()->startOfMonth(),
            'position_id' => $position->id,
            'employment_status_id' => $employmentStatus->id,
            'is_active' => true,
        ], $attributes));
    }
}
