<?php

namespace Tests\Feature;

use App\Filament\Resources\Positions\PositionResource;
use App\Filament\Resources\Positions\RelationManagers\EmployeesRelationManager;
use App\Models\Employee;
use App\Models\EmploymentStatus;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PositionEmployeeVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_positions_resource_counts_only_active_employees_and_registers_employee_relation_manager(): void
    {
        $position = Position::query()->create([
            'name' => 'Mandor',
            'code' => 'MDR',
            'is_active' => true,
        ]);

        $this->createEmployee($position, ['nik_sap' => '13001001', 'is_active' => true]);
        $this->createEmployee($position, ['nik_sap' => '13001002', 'is_active' => true]);
        $this->createEmployee($position, ['nik_sap' => '13001003', 'is_active' => false]);

        $this->assertSame(
            2,
            Position::query()
                ->whereKey($position->id)
                ->withCount(['employees' => fn ($query) => $query->active()])
                ->firstOrFail()
                ->employees_count,
        );

        $this->assertContains(EmployeesRelationManager::class, PositionResource::getRelations());
    }

    public function test_position_employee_relation_manager_tabs_split_active_and_all_employees(): void
    {
        $position = Position::query()->create([
            'name' => 'Staff',
            'code' => 'STF',
            'is_active' => true,
        ]);

        $activeEmployee = $this->createEmployee($position, ['nik_sap' => '13002001', 'is_active' => true]);
        $inactiveEmployee = $this->createEmployee($position, ['nik_sap' => '13002002', 'is_active' => false]);

        $manager = new class extends EmployeesRelationManager
        {
            public function ownerRecordForTest(Position $position): void
            {
                $this->ownerRecord = $position;
            }
        };

        $manager->ownerRecordForTest($position);

        $tabs = $manager->getTabs();

        $this->assertSame(
            [$activeEmployee->id],
            $tabs['active']->modifyQuery(Employee::query()->where('position_id', $position->id))->pluck('id')->all(),
        );

        $allIds = $tabs['all']->modifyQuery(Employee::query()->where('position_id', $position->id))->pluck('id')->all();
        sort($allIds);

        $expectedIds = [$activeEmployee->id, $inactiveEmployee->id];
        sort($expectedIds);

        $this->assertSame($expectedIds, $allIds);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createEmployee(Position $position, array $attributes = []): Employee
    {
        $status = EmploymentStatus::query()->firstOrCreate([
            'name' => 'Tetap',
        ], [
            'color' => 'success',
            'is_active' => true,
        ]);

        return Employee::query()->create(array_merge([
            'nik_sap' => fake()->unique()->numerify('########'),
            'nik_karyawan' => fake()->unique()->numerify('################'),
            'full_name' => fake()->name(),
            'hire_date' => now()->startOfMonth(),
            'position_id' => $position->id,
            'employment_status_id' => $status->id,
            'is_active' => true,
        ], $attributes));
    }
}
