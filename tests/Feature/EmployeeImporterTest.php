<?php

namespace Tests\Feature;

use App\Filament\Imports\EmployeeImporter;
use App\Models\Employee;
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
        $this->assertSame('Belum Diisi', $employee->position->name);
        $this->assertSame('Belum Diisi', $employee->employmentStatus->name);
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
