<?php

namespace Tests\Feature;

use App\Filament\Imports\EmployeeFamilyImporter;
use App\Models\Employee;
use App\Models\EmploymentStatus;
use App\Models\Position;
use App\Models\User;
use Filament\Actions\Imports\Jobs\ImportCsv;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeFamilyImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_family_import_connects_to_employee_by_nik_sap_and_accepts_sample_headers(): void
    {
        $employee = $this->createEmployee('13004844');

        $import = $this->runImport([
            [
                'No' => '2',
                'NIK_SAP' => ' 13004844 ',
                'Nama anggota keluarga' => 'Siti Ramdawati',
                'Status' => 'Istri',
                'Gender' => 'P:',
                'Tempat lahir' => 'Putussibau',
                'Tanggal Lahir' => '13/04/1990',
                'Pendidikan Terakhir' => '',
                'Agama' => 'Islam',
                'Suku' => 'Melayu',
                'Alamat' => '',
            ],
            [
                'No' => '3',
                'NIK_SAP' => '13004844.0',
                'Nama anggota keluarga' => 'Muhammad Refan Akbar',
                'Status' => 'Anak',
                'Gender' => 'L',
                'Tempat lahir' => 'Pasir',
                'Tanggal Lahir' => '17/07/2012',
                'Pendidikan Terakhir' => 'SMP',
                'Agama' => 'Islam',
                'Suku' => 'Dayak',
                'Alamat' => '',
            ],
        ]);

        $this->assertSame(2, $import->successful_rows);
        $this->assertCount(0, $import->failedRows);

        $this->assertDatabaseHas('employee_families', [
            'employee_id' => $employee->id,
            'name' => 'Siti Ramdawati',
            'relationship' => 'Istri',
            'gender' => 'P',
            'birth_place' => 'Putussibau',
            'birth_date' => '1990-04-13 00:00:00',
            'religion' => 'Islam',
            'ethnicity' => 'Melayu',
        ]);

        $this->assertDatabaseHas('employee_families', [
            'employee_id' => $employee->id,
            'name' => 'Muhammad Refan Akbar',
            'relationship' => 'Anak',
            'gender' => 'L',
            'last_education' => 'SMP',
        ]);
    }

    private function runImport(array $rows): Import
    {
        $user = User::factory()->create();

        $import = Import::query()->create([
            'file_name' => 'employee-families.csv',
            'file_path' => 'imports/employee-families.csv',
            'importer' => EmployeeFamilyImporter::class,
            'processed_rows' => 0,
            'total_rows' => count($rows),
            'successful_rows' => 0,
            'user_id' => $user->id,
        ]);

        $job = new ImportCsv($import, $rows, [
            'employee' => 'NIK_SAP',
            'name' => 'Nama anggota keluarga',
            'relationship' => 'Status',
            'gender' => 'Gender',
            'birth_place' => 'Tempat lahir',
            'birth_date' => 'Tanggal Lahir',
            'last_education' => 'Pendidikan Terakhir',
            'religion' => 'Agama',
            'ethnicity' => 'Suku',
            'address' => 'Alamat',
        ]);

        $job->handle();

        return $import->refresh()->load('failedRows');
    }

    private function createEmployee(string $nikSap): Employee
    {
        $position = Position::query()->create([
            'name' => 'Staff',
            'is_active' => true,
        ]);

        $employmentStatus = EmploymentStatus::query()->create([
            'name' => 'Tetap',
            'color' => 'info',
            'is_active' => true,
        ]);

        return Employee::query()->create([
            'nik_sap' => $nikSap,
            'nik_karyawan' => '000.0000.0000.0001',
            'full_name' => 'Karyawan Sample',
            'hire_date' => '2020-01-01',
            'position_id' => $position->id,
            'employment_status_id' => $employmentStatus->id,
            'is_active' => true,
        ]);
    }
}
