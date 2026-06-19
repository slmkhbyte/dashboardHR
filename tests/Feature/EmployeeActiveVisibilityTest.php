<?php

namespace Tests\Feature;

use App\Filament\Resources\Employees\Pages\ListEmployees;
use App\Filament\Widgets\EmployeeCategoryBarChart;
use App\Filament\Widgets\EmployeeDistributionChart;
use App\Filament\Widgets\EmployeeLevelBodBarChart;
use App\Filament\Widgets\EmployeeSapWorkUnitComparison;
use App\Filament\Widgets\EmployeeTrendLineChart;
use App\Filament\Widgets\HrStatsOverview;
use App\Models\Employee;
use App\Models\EmploymentStatus;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeActiveVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_scopes_and_list_tabs_separate_active_and_inactive_records(): void
    {
        $activeEmployee = $this->createEmployee([
            'nik_sap' => '13000001',
            'full_name' => 'Active Employee',
            'is_active' => true,
        ]);

        $inactiveEmployee = $this->createEmployee([
            'nik_sap' => '13000002',
            'full_name' => 'Inactive Employee',
            'is_active' => false,
        ]);

        $this->assertSame([$activeEmployee->id], Employee::query()->active()->pluck('id')->all());
        $this->assertSame([$inactiveEmployee->id], Employee::query()->inactive()->pluck('id')->all());

        $page = app(ListEmployees::class);
        $tabs = $page->getTabs();

        $this->assertSame([$activeEmployee->id], $tabs['active']->modifyQuery(Employee::query())->pluck('id')->all());
        $this->assertSame([$inactiveEmployee->id], $tabs['inactive']->modifyQuery(Employee::query())->pluck('id')->all());
        $allIds = $tabs['all']->modifyQuery(Employee::query())->pluck('id')->all();
        sort($allIds);

        $expectedIds = [$activeEmployee->id, $inactiveEmployee->id];
        sort($expectedIds);

        $this->assertSame($expectedIds, $allIds);
    }

    public function test_hr_employee_widgets_only_count_active_employees(): void
    {
        $activeStatus = EmploymentStatus::query()->create([
            'name' => 'Tetap',
            'color' => 'success',
            'is_active' => true,
        ]);

        $inactiveStatus = EmploymentStatus::query()->create([
            'name' => 'Kontrak',
            'color' => 'warning',
            'is_active' => true,
        ]);

        $activePosition = Position::query()->create([
            'name' => 'Mandor',
            'code' => 'MDR',
            'is_active' => true,
        ]);

        $inactivePosition = Position::query()->create([
            'name' => 'Admin',
            'code' => 'ADM',
            'is_active' => true,
        ]);

        $this->createEmployee([
            'nik_sap' => '13000003',
            'full_name' => 'Active One',
            'employment_status_id' => $activeStatus->id,
            'position_id' => $activePosition->id,
            'work_unit' => 'AFDELING I',
            'lvl_bod' => 2,
            'hire_date' => now()->subMonths(2)->startOfMonth(),
            'is_active' => true,
        ]);

        $this->createEmployee([
            'nik_sap' => '13000004',
            'full_name' => 'Active Two',
            'employment_status_id' => $activeStatus->id,
            'position_id' => $activePosition->id,
            'work_unit' => 'AFDELING I',
            'lvl_bod' => 2,
            'hire_date' => now()->subMonth()->startOfMonth(),
            'is_active' => true,
        ]);

        $this->createEmployee([
            'nik_sap' => '13000005',
            'full_name' => 'Inactive One',
            'employment_status_id' => $inactiveStatus->id,
            'position_id' => $inactivePosition->id,
            'work_unit' => 'AFDELING II',
            'lvl_bod' => 7,
            'hire_date' => now()->subMonth()->startOfMonth(),
            'is_active' => false,
        ]);

        $statsWidget = new class extends HrStatsOverview
        {
            public function exposeStats(): array
            {
                return $this->getStats();
            }
        };

        $distributionWidget = new class extends EmployeeDistributionChart
        {
            public function exposeData(): array
            {
                return $this->getData();
            }
        };

        $categoryWidget = new class extends EmployeeCategoryBarChart
        {
            public function exposeData(): array
            {
                return $this->getData();
            }
        };

        $levelWidget = new class extends EmployeeLevelBodBarChart
        {
            public function exposeData(): array
            {
                return $this->getData();
            }
        };

        $trendWidget = new class extends EmployeeTrendLineChart
        {
            public function exposeData(): array
            {
                return $this->getData();
            }
        };

        $stats = $statsWidget->exposeStats();
        $distributionData = $distributionWidget->exposeData();
        $categoryData = $categoryWidget->exposeData();
        $levelData = $levelWidget->exposeData();
        $trendData = $trendWidget->exposeData();

        $this->assertSame('2', $stats[0]->getValue());
        $this->assertSame(['Tetap'], $distributionData['labels']);
        $this->assertSame([2], $distributionData['datasets'][0]['data']);
        $this->assertSame(['AFDELING I'], $categoryData['labels']);
        $this->assertSame([2], $categoryData['datasets'][0]['data']);
        $this->assertSame(['2'], $levelData['labels']);
        $this->assertSame([2], $levelData['datasets'][0]['data']);
        $this->assertSame(2, array_sum($trendData['datasets'][0]['data']));
    }

    public function test_hr_dashboard_widgets_refresh_periodically(): void
    {
        $widgetClasses = [
            HrStatsOverview::class,
            EmployeeDistributionChart::class,
            EmployeeCategoryBarChart::class,
            EmployeeLevelBodBarChart::class,
            EmployeeTrendLineChart::class,
            EmployeeSapWorkUnitComparison::class,
        ];

        foreach ($widgetClasses as $widgetClass) {
            $property = new \ReflectionProperty($widgetClass, 'pollingInterval');

            $this->assertSame('10s', $property->getValue(app($widgetClass)));
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createEmployee(array $attributes = []): Employee
    {
        $position = $attributes['position_id'] ?? Position::query()->create([
            'name' => 'Position ' . ($attributes['nik_sap'] ?? fake()->unique()->numerify('########')),
            'code' => 'POS' . fake()->unique()->numerify('###'),
            'is_active' => true,
        ])->id;

        $employmentStatus = $attributes['employment_status_id'] ?? EmploymentStatus::query()->create([
            'name' => 'Status ' . ($attributes['nik_sap'] ?? fake()->unique()->numerify('########')),
            'color' => 'gray',
            'is_active' => true,
        ])->id;

        return Employee::query()->create(array_merge([
            'nik_sap' => fake()->unique()->numerify('########'),
            'nik_karyawan' => fake()->unique()->numerify('################'),
            'full_name' => fake()->name(),
            'hire_date' => now()->startOfMonth(),
            'position_id' => $position,
            'employment_status_id' => $employmentStatus,
            'is_active' => true,
        ], $attributes));
    }
}
