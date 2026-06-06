<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmploymentStatus;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EmploymentStatusDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_employment_status_is_created_once_and_reused(): void
    {
        $first = EmploymentStatus::getOrCreateDefault();
        $second = EmploymentStatus::getOrCreateDefault();

        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('employment_statuses', 1);
        $this->assertSame(EmploymentStatus::DEFAULT_NAME, $first->name);
    }

    public function test_deleting_an_employment_status_reassigns_employees_to_default_status(): void
    {
        $defaultStatus = EmploymentStatus::getOrCreateDefault();
        $employmentStatus = EmploymentStatus::query()->create([
            'name' => 'Kontrak',
            'color' => 'warning',
            'is_active' => true,
        ]);

        $employee = $this->createEmployee($employmentStatus);

        $employmentStatus->delete();

        $this->assertModelMissing($employmentStatus);
        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'employment_status_id' => $defaultStatus->id,
        ]);
    }

    public function test_default_employment_status_cannot_be_deleted(): void
    {
        $defaultStatus = EmploymentStatus::getOrCreateDefault();
        $employee = $this->createEmployee($defaultStatus);

        try {
            $defaultStatus->delete();
            $this->fail('Default employment status deletion should have been blocked.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'Status default Tanpa Status Kerja tidak boleh dihapus.',
                $exception->errors()['employment_status'][0] ?? null,
            );
        }

        $this->assertModelExists($defaultStatus);
        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'employment_status_id' => $defaultStatus->id,
        ]);
    }

    private function createEmployee(EmploymentStatus $employmentStatus): Employee
    {
        $position = Position::query()->create([
            'name' => 'Staff',
            'code' => 'STF',
            'is_active' => true,
        ]);

        return Employee::query()->create([
            'nik_sap' => fake()->unique()->numerify('########'),
            'nik_karyawan' => fake()->unique()->numerify('################'),
            'full_name' => fake()->name(),
            'hire_date' => now()->startOfDay(),
            'position_id' => $position->id,
            'employment_status_id' => $employmentStatus->id,
            'is_active' => true,
        ]);
    }
}
