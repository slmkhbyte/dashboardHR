<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmploymentStatus;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PositionDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_position_is_created_once_and_reused(): void
    {
        $first = Position::getOrCreateDefault();
        $second = Position::getOrCreateDefault();

        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('positions', 1);
        $this->assertSame(Position::DEFAULT_NAME, $first->name);
    }

    public function test_deleting_a_position_reassigns_employees_to_default_position(): void
    {
        $defaultPosition = Position::getOrCreateDefault();
        $position = Position::query()->create([
            'name' => 'Mandor',
            'code' => 'MDR',
            'is_active' => true,
        ]);

        $employee = $this->createEmployee($position);

        $position->delete();

        $this->assertModelMissing($position);
        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'position_id' => $defaultPosition->id,
        ]);
    }

    public function test_default_position_cannot_be_deleted(): void
    {
        $defaultPosition = Position::getOrCreateDefault();
        $employee = $this->createEmployee($defaultPosition);

        try {
            $defaultPosition->delete();
            $this->fail('Default position deletion should have been blocked.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'Jabatan default Tanpa Jabatan tidak boleh dihapus.',
                $exception->errors()['position'][0] ?? null,
            );
        }

        $this->assertModelExists($defaultPosition);
        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'position_id' => $defaultPosition->id,
        ]);
    }

    private function createEmployee(Position $position): Employee
    {
        $status = EmploymentStatus::query()->create([
            'name' => 'Tetap',
            'color' => 'success',
            'is_active' => true,
        ]);

        return Employee::query()->create([
            'nik_sap' => fake()->unique()->numerify('########'),
            'nik_karyawan' => fake()->unique()->numerify('################'),
            'full_name' => fake()->name(),
            'hire_date' => now()->startOfDay(),
            'position_id' => $position->id,
            'employment_status_id' => $status->id,
            'is_active' => true,
        ]);
    }
}
