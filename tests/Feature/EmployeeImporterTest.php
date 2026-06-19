<?php

namespace Tests\Feature;

use App\Filament\Imports\EmployeeImporter;
use App\Models\Employee;
use App\Models\EmploymentStatus;
use App\Models\Position;
use App\Models\User;
use Filament\Actions\Imports\Jobs\ImportCsv;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_import_accepts_minimal_nik_sap_rows_for_family_connector(): void
    {
        $import = $this->runImport([
            [
                'NIK_SAP' => ' 13004844.0 ',
            ],
        ], [
            'nik_sap' => 'NIK_SAP',
        ]);

        $this->assertSame(1, $import->successful_rows);
        $this->assertCount(0, $import->failedRows);

        $employee = Employee::query()->where('nik_sap', '13004844')->firstOrFail();

        $this->assertSame('Karyawan 13004844', $employee->full_name);
        $this->assertSame(Position::DEFAULT_NAME, $employee->position->name);
        $this->assertSame(EmploymentStatus::DEFAULT_NAME, $employee->employmentStatus->name);
        $this->assertNotNull($employee->hire_date);
    }

    public function test_employee_import_accepts_common_indonesian_headers(): void
    {
        $import = $this->runImport([
            [
                'NIK_SAP' => '13004845',
                'Nama Lengkap' => 'Rachmadi',
                'Gender' => 'L',
                'Agama' => 'Islam',
                'Tempat Lahir' => 'Putussibau',
                'Tanggal Lahir' => '13/04/1990',
                'Pendidikan Terakhir' => 'SMA',
                'tmt Bekerja' => '01/02/2025',
                'JABATAN' => 'Staff',
                'STATUS' => 'Tetap',
                'BAGIAN' => 'AFDELING I',
                'LVL BOD' => '6',
            ],
        ], [
            'nik_sap' => 'NIK_SAP',
            'full_name' => 'Nama Lengkap',
            'gender' => 'Gender',
            'religion' => 'Agama',
            'birth_place' => 'Tempat Lahir',
            'birth_date' => 'Tanggal Lahir',
            'last_education' => 'Pendidikan Terakhir',
            'hire_date' => 'tmt Bekerja',
            'position' => 'JABATAN',
            'employment_status' => 'STATUS',
            'work_unit' => 'BAGIAN',
            'lvl_bod' => 'LVL BOD',
        ]);

        $this->assertSame(1, $import->successful_rows);
        $this->assertCount(0, $import->failedRows);

        $this->assertDatabaseHas('employees', [
            'nik_sap' => '13004845',
            'full_name' => 'Rachmadi',
            'gender' => 'Laki-laki',
            'birth_date' => '1990-04-13 00:00:00',
            'hire_date' => '2025-02-01 00:00:00',
            'work_unit' => 'AFDELING I',
            'lvl_bod' => 6,
        ]);
    }

    public function test_employee_import_treats_blank_employee_nik_values_as_null(): void
    {
        $import = $this->runImport([
            [
                'nik_sap' => '13004848',
                'nik' => '',
                'nama' => 'Tanpa NIK Satu',
            ],
            [
                'nik_sap' => '13004849',
                'nik' => '   ',
                'nama' => 'Tanpa NIK Dua',
            ],
        ], [
            'nik_sap' => 'nik_sap',
            'nik_karyawan' => 'nik',
            'full_name' => 'nama',
        ]);

        $this->assertSame(2, $import->successful_rows);
        $this->assertCount(0, $import->failedRows);

        $this->assertDatabaseHas('employees', [
            'nik_sap' => '13004848',
            'nik_karyawan' => null,
            'full_name' => 'Tanpa NIK Satu',
        ]);

        $this->assertDatabaseHas('employees', [
            'nik_sap' => '13004849',
            'nik_karyawan' => null,
            'full_name' => 'Tanpa NIK Dua',
        ]);
    }

    public function test_employee_import_keeps_defaults_when_required_default_columns_are_blank(): void
    {
        $import = $this->runImport([
            [
                'nik_sap' => '19017762',
                'nama' => 'HOLAN SUPARTO',
                'dependent_count' => '',
                'is_active' => '',
            ],
        ], [
            'nik_sap' => 'nik_sap',
            'full_name' => 'nama',
            'dependent_count' => 'dependent_count',
            'is_active' => 'is_active',
        ]);

        $this->assertSame(1, $import->successful_rows);
        $this->assertCount(0, $import->failedRows);

        $this->assertDatabaseHas('employees', [
            'nik_sap' => '19017762',
            'full_name' => 'HOLAN SUPARTO',
            'dependent_count' => 0,
            'is_active' => true,
        ]);
    }

    public function test_employee_import_marks_duplicate_employee_nik_as_failed_row_before_saving(): void
    {
        $position = Position::getOrCreateDefault();
        $employmentStatus = EmploymentStatus::getOrCreateDefault();

        Employee::query()->create([
            'nik_sap' => '13004850',
            'nik_karyawan' => '6103204107710123',
            'full_name' => 'Karyawan Lama',
            'hire_date' => now()->toDateString(),
            'position_id' => $position->id,
            'employment_status_id' => $employmentStatus->id,
            'is_active' => true,
        ]);

        $import = $this->runImport([
            [
                'nik_sap' => '13004851',
                'nik' => '6103204107710123',
                'nama' => 'NIK Duplikat',
            ],
            [
                'nik_sap' => '13004852',
                'nik' => '6103204107710124',
                'nama' => 'NIK Baru',
            ],
        ], [
            'nik_sap' => 'nik_sap',
            'nik_karyawan' => 'nik',
            'full_name' => 'nama',
        ]);

        $this->assertSame(1, $import->successful_rows);
        $this->assertCount(1, $import->failedRows);
        $this->assertStringContainsString('NIK Karyawan sudah dipakai', $import->failedRows->first()->validation_error ?? '');

        $this->assertDatabaseMissing('employees', [
            'nik_sap' => '13004851',
        ]);

        $this->assertDatabaseHas('employees', [
            'nik_sap' => '13004852',
            'nik_karyawan' => '6103204107710124',
            'full_name' => 'NIK Baru',
        ]);
    }

    public function test_employee_import_accepts_provided_employee_csv_headers(): void
    {
        $import = $this->runImport([
            [
                'nik_sap' => '13000221',
                'nik' => '6103204107710123',
                'nama' => 'Tusini',
                'Golongan' => 'IB/13',
                'Tanggungan' => 'K/0',
                'Gender' => 'P',
                'Tempat Lahir' => 'Gunung Kidul',
                'Tgl Lahir' => '01/07/1971',
                'Pendidikan terakhir' => 'SD',
                'agama' => 'Islam',
                'no telp' => '082153482252',
                'Email' => 'sumarjonojono401@gmail.com',
                'tmt Bekerja' => '09/05/1997',
                'STATUS' => 'Karpel - Tetap',
                'LVL BOD' => '6',
                'JABATAN' => 'PEMELIHARAAN',
                'BAGIAN' => 'AFDELING I',
            ],
            [
                'nik_sap' => '13000466',
                'nik' => '6103200304720003',
                'nama' => 'Subehi',
                'Golongan' => 'IC/00',
                'Tanggungan' => 'K/1',
                'Gender' => 'L',
                'Tempat Lahir' => 'Wonosobo',
                'Tgl Lahir' => '01/07/1972',
                'Pendidikan terakhir' => 'SD',
                'agama' => 'Islam',
                'no telp' => '085845285932',
                'Email' => 'subhisubhi313@gmail.com',
                'tmt Bekerja' => '21/01/1991',
                'STATUS' => 'Karpel - Tetap',
                'LVL BOD' => '6',
                'JABATAN' => 'PEMANEN',
                'BAGIAN' => 'AFDELING VII',
            ],
        ], [
            'nik_sap' => 'nik_sap',
            'nik_karyawan' => 'nik',
            'full_name' => 'nama',
            'employee_grade' => 'Golongan',
            'marital_status' => 'Tanggungan',
            'gender' => 'Gender',
            'birth_place' => 'Tempat Lahir',
            'birth_date' => 'Tgl Lahir',
            'last_education' => 'Pendidikan terakhir',
            'religion' => 'agama',
            'phone' => 'no telp',
            'email' => 'Email',
            'hire_date' => 'tmt Bekerja',
            'employment_status' => 'STATUS',
            'lvl_bod' => 'LVL BOD',
            'position' => 'JABATAN',
            'work_unit' => 'BAGIAN',
        ]);

        $this->assertSame(2, $import->successful_rows);
        $this->assertCount(0, $import->failedRows);

        $this->assertDatabaseHas('employees', [
            'nik_sap' => '13000221',
            'nik_karyawan' => '6103204107710123',
            'full_name' => 'Tusini',
            'employee_grade' => 'IB/13',
            'marital_status' => 'K',
            'dependent_count' => 0,
            'gender' => 'Perempuan',
            'birth_place' => 'Gunung Kidul',
            'birth_date' => '1971-07-01 00:00:00',
            'last_education' => 'SD',
            'religion' => 'Islam',
            'phone' => '082153482252',
            'email' => 'sumarjonojono401@gmail.com',
            'hire_date' => '1997-05-09 00:00:00',
            'work_unit' => 'AFDELING I',
            'lvl_bod' => 6,
        ]);

        $this->assertDatabaseHas('employees', [
            'nik_sap' => '13000466',
            'marital_status' => 'K',
            'dependent_count' => 1,
            'gender' => 'Laki-laki',
            'hire_date' => '1991-01-21 00:00:00',
            'work_unit' => 'AFDELING VII',
        ]);
    }

    public function test_employee_import_preserves_freeform_last_education_values(): void
    {
        $import = $this->runImport([
            [
                'NIK_SAP' => '13004846',
                'Nama Lengkap' => 'Sulastri',
                'Pendidikan Terakhir' => '  SD Negeri  ',
                'tmt Bekerja' => '01/03/2025',
                'JABATAN' => 'Staff',
                'STATUS' => 'Tetap',
            ],
            [
                'NIK_SAP' => '13004847',
                'Nama Lengkap' => 'Sunaryo',
                'Pendidikan Terakhir' => 'S1 Teknik Pertanian',
                'tmt Bekerja' => '01/04/2025',
                'JABATAN' => 'Mandor',
                'STATUS' => 'Kontrak',
            ],
        ], [
            'nik_sap' => 'NIK_SAP',
            'full_name' => 'Nama Lengkap',
            'last_education' => 'Pendidikan Terakhir',
            'hire_date' => 'tmt Bekerja',
            'position' => 'JABATAN',
            'employment_status' => 'STATUS',
        ]);

        $this->assertSame(2, $import->successful_rows);
        $this->assertCount(0, $import->failedRows);

        $this->assertDatabaseHas('employees', [
            'nik_sap' => '13004846',
            'last_education' => 'SD Negeri',
        ]);

        $this->assertDatabaseHas('employees', [
            'nik_sap' => '13004847',
            'last_education' => 'S1 Teknik Pertanian',
        ]);
    }

    private function runImport(array $rows, array $columnMap): Import
    {
        $user = User::factory()->create();

        $import = Import::query()->create([
            'file_name' => 'employees.csv',
            'file_path' => 'imports/employees.csv',
            'importer' => EmployeeImporter::class,
            'processed_rows' => 0,
            'total_rows' => count($rows),
            'successful_rows' => 0,
            'user_id' => $user->id,
        ]);

        $job = new ImportCsv($import, $rows, $columnMap);

        $job->handle();

        return $import->refresh()->load('failedRows');
    }
}
