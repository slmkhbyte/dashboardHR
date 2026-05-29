<?php

namespace Tests\Feature;

use App\Filament\Imports\EmployeeFamilyImporter;
use App\Filament\Imports\EmployeeImporter;
use App\Models\Employee;
use App\Models\Division;
use App\Models\EmploymentStatus;
use App\Models\Position;
use App\Models\User;
use DateTimeImmutable;
use Filament\Actions\Imports\Jobs\ImportCsv;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImporterDateParsingTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_import_accepts_localized_and_iso_dates(): void
    {
        $this->seedEmployeeImportReferences();

        $import = $this->runImport(EmployeeImporter::class, [
            [
                'nik' => 'EMP-100',
                'full_name' => 'Tanggal Lokal',
                'birth_date' => '12/03/1994',
                'hire_date' => '01/02/2025',
                'division' => 'Human Resources',
                'position' => 'HR Manager',
                'employment_status' => 'Tetap',
                'is_active' => 'true',
            ],
            [
                'nik' => 'EMP-101',
                'full_name' => 'Tanggal ISO',
                'birth_date' => '1994-03-12',
                'hire_date' => '2025-12-25',
                'division' => 'Finance',
                'position' => 'Finance Analyst',
                'employment_status' => 'Kontrak',
                'is_active' => 'true',
            ],
        ]);

        $this->assertSame(2, $import->successful_rows);
        $this->assertDatabaseHas('employees', [
            'nik' => 'EMP-100',
            'birth_date' => '1994-03-12 00:00:00',
            'hire_date' => '2025-02-01 00:00:00',
        ]);
        $this->assertDatabaseHas('employees', [
            'nik' => 'EMP-101',
            'birth_date' => '1994-03-12 00:00:00',
            'hire_date' => '2025-12-25 00:00:00',
        ]);
    }

    public function test_employee_import_accepts_excel_serial_dates(): void
    {
        $this->seedEmployeeImportReferences();

        $import = $this->runImport(EmployeeImporter::class, [
            [
                'nik' => 'EMP-102',
                'full_name' => 'Tanggal Serial',
                'birth_date' => $this->excelSerialForDate('1994-03-12'),
                'hire_date' => $this->excelSerialForDate('2025-12-25'),
                'division' => 'Operations',
                'position' => 'Operations Supervisor',
                'employment_status' => 'Tetap',
                'is_active' => 'true',
            ],
        ]);

        $this->assertSame(1, $import->successful_rows);
        $this->assertDatabaseHas('employees', [
            'nik' => 'EMP-102',
            'birth_date' => '1994-03-12 00:00:00',
            'hire_date' => '2025-12-25 00:00:00',
        ]);
    }

    public function test_employee_import_accepts_short_excel_style_dates(): void
    {
        $this->seedEmployeeImportReferences();

        $import = $this->runImport(EmployeeImporter::class, [
            [
                'nik' => 'EMP-104',
                'full_name' => 'Tanggal Excel Pendek',
                'birth_date' => '03-12-94',
                'hire_date' => '05-28-26',
                'division' => 'Technology',
                'position' => 'Software Engineer',
                'employment_status' => 'Tetap',
                'is_active' => 'true',
            ],
        ]);

        $this->assertSame(1, $import->successful_rows);
        $this->assertDatabaseHas('employees', [
            'nik' => 'EMP-104',
            'birth_date' => '1994-03-12 00:00:00',
            'hire_date' => '2026-05-28 00:00:00',
        ]);
    }

    public function test_employee_family_import_accepts_excel_style_birth_dates(): void
    {
        $division = Division::query()->create([
            'name' => 'Human Resources',
            'is_active' => true,
        ]);
        $position = Position::query()->create([
            'name' => 'HR Manager',
            'is_active' => true,
        ]);
        $employmentStatus = EmploymentStatus::query()->create([
            'name' => 'Tetap',
            'color' => 'info',
            'is_active' => true,
        ]);

        $employee = Employee::query()->create([
            'nik' => 'EMP-200',
            'full_name' => 'Parent Employee',
            'hire_date' => '2025-01-01',
            'division_id' => $division->id,
            'position_id' => $position->id,
            'employment_status_id' => $employmentStatus->id,
            'is_active' => true,
        ]);

        $import = $this->runImport(EmployeeFamilyImporter::class, [
            [
                'employee_nik' => $employee->nik,
                'family_name' => 'Nama Pasangan',
                'relationship' => 'Pasangan',
                'birth_date' => '10.04.1995',
                'is_dependent' => 'true',
            ],
        ], [
            'employee' => 'employee_nik',
            'name' => 'family_name',
            'relationship' => 'relationship',
            'birth_date' => 'birth_date',
            'phone' => 'phone',
            'is_dependent' => 'is_dependent',
            'notes' => 'notes',
        ]);

        $this->assertSame(1, $import->successful_rows);
        $this->assertDatabaseHas('employee_families', [
            'employee_id' => $employee->id,
            'name' => 'Nama Pasangan',
            'birth_date' => '1995-04-10 00:00:00',
        ]);
    }

    public function test_invalid_dates_still_fail_and_are_logged(): void
    {
        $this->seedEmployeeImportReferences();

        $import = $this->runImport(EmployeeImporter::class, [
            [
                'nik' => 'EMP-103',
                'full_name' => 'Tanggal Gagal',
                'birth_date' => 'abc',
                'hire_date' => 'abc',
                'division' => 'Human Resources',
                'position' => 'HR Manager',
                'employment_status' => 'Tetap',
                'is_active' => 'true',
            ],
        ]);

        $this->assertSame(0, $import->successful_rows);
        $this->assertCount(1, $import->failedRows);
        $this->assertSame('abc', $import->failedRows->first()->data['hire_date']);
        $this->assertStringContainsString('date', strtolower($import->failedRows->first()->validation_error ?? ''));
        $this->assertDatabaseMissing('employees', [
            'nik' => 'EMP-103',
        ]);
    }

    private function runImport(string $importer, array $rows, ?array $columnMap = null): Import
    {
        $user = User::factory()->create();

        $import = Import::query()->create([
            'file_name' => 'test-import.csv',
            'file_path' => 'imports/test-import.csv',
            'importer' => $importer,
            'processed_rows' => 0,
            'total_rows' => count($rows),
            'successful_rows' => 0,
            'user_id' => $user->id,
        ]);

        $job = new ImportCsv(
            $import,
            $rows,
            $columnMap ?? $this->defaultColumnMap(),
        );

        $job->handle();

        return $import->refresh()->load('failedRows');
    }

    /**
     * @return array<string, string>
     */
    private function defaultColumnMap(): array
    {
        return [
            'nik' => 'nik',
            'full_name' => 'full_name',
            'email' => 'email',
            'phone' => 'phone',
            'gender' => 'gender',
            'birth_date' => 'birth_date',
            'hire_date' => 'hire_date',
            'address' => 'address',
            'is_active' => 'is_active',
            'division' => 'division',
            'position' => 'position',
            'employment_status' => 'employment_status',
        ];
    }

    private function excelSerialForDate(string $date): int
    {
        $baseDate = new DateTimeImmutable('1899-12-30');
        $targetDate = new DateTimeImmutable($date);

        return $baseDate->diff($targetDate)->days;
    }

    private function seedEmployeeImportReferences(): void
    {
        Division::query()->firstOrCreate([
            'name' => 'Human Resources',
        ], [
            'is_active' => true,
        ]);
        Division::query()->firstOrCreate([
            'name' => 'Finance',
        ], [
            'is_active' => true,
        ]);
        Division::query()->firstOrCreate([
            'name' => 'Operations',
        ], [
            'is_active' => true,
        ]);
        Division::query()->firstOrCreate([
            'name' => 'Technology',
        ], [
            'is_active' => true,
        ]);

        Position::query()->firstOrCreate([
            'name' => 'HR Manager',
        ], [
            'is_active' => true,
        ]);
        Position::query()->firstOrCreate([
            'name' => 'Finance Analyst',
        ], [
            'is_active' => true,
        ]);
        Position::query()->firstOrCreate([
            'name' => 'Operations Supervisor',
        ], [
            'is_active' => true,
        ]);
        Position::query()->firstOrCreate([
            'name' => 'Software Engineer',
        ], [
            'is_active' => true,
        ]);

        EmploymentStatus::query()->firstOrCreate([
            'name' => 'Tetap',
        ], [
            'color' => 'info',
            'is_active' => true,
        ]);
        EmploymentStatus::query()->firstOrCreate([
            'name' => 'Kontrak',
        ], [
            'color' => 'warning',
            'is_active' => true,
        ]);
    }
}
