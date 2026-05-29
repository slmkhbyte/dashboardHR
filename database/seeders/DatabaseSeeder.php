<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmployeeFamily;
use App\Models\EmploymentStatus;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = collect([
            ['name' => 'Tetap', 'color' => 'success', 'description' => 'Karyawan permanen'],
            ['name' => 'Kontrak', 'color' => 'warning', 'description' => 'Karyawan kontrak'],
            ['name' => 'Probation', 'color' => 'info', 'description' => 'Masa percobaan'],
        ])->map(fn (array $status) => EmploymentStatus::query()->firstOrCreate(
            ['name' => $status['name']],
            $status + ['is_active' => true],
        ));

        $divisions = collect([
            ['name' => 'Human Resources', 'code' => 'HR'],
            ['name' => 'Finance', 'code' => 'FIN'],
            ['name' => 'Operations', 'code' => 'OPS'],
            ['name' => 'Technology', 'code' => 'TECH'],
            ['name' => 'Sales', 'code' => 'SLS'],
            ['name' => 'Customer Support', 'code' => 'CS'],
        ])->map(fn (array $division) => Division::query()->firstOrCreate(
            ['name' => $division['name']],
            $division + ['is_active' => true],
        ));

        $positions = collect([
            ['name' => 'HR Manager', 'code' => 'HRM'],
            ['name' => 'Finance Analyst', 'code' => 'FNA'],
            ['name' => 'Operations Supervisor', 'code' => 'OPS-SPV'],
            ['name' => 'Software Engineer', 'code' => 'SWE'],
            ['name' => 'Recruiter', 'code' => 'RCT'],
            ['name' => 'Payroll Officer', 'code' => 'PAY'],
            ['name' => 'Accountant', 'code' => 'ACC'],
            ['name' => 'Product Manager', 'code' => 'PM'],
            ['name' => 'QA Engineer', 'code' => 'QA'],
            ['name' => 'Sales Executive', 'code' => 'SE'],
            ['name' => 'Customer Support Specialist', 'code' => 'CSS'],
            ['name' => 'Warehouse Coordinator', 'code' => 'WHC'],
        ])->map(fn (array $position) => Position::query()->firstOrCreate(
            ['name' => $position['name']],
            $position + ['is_active' => true],
        ));

        User::query()->updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Admin HR',
            'password' => Hash::make('password'),
        ]);

        if (Employee::query()->exists()) {
            return;
        }

        $divisionMap = $divisions->keyBy('code');
        $positionMap = $positions->keyBy('code');
        $statusMap = $statuses->keyBy('name');

        $employeeBlueprints = [
            ['full_name' => 'Alya Pratama', 'gender' => 'Perempuan', 'division' => 'HR', 'position' => 'HRM', 'status' => 'Tetap', 'city' => 'Jakarta', 'hire_months_ago' => 18, 'birth_date' => '1994-03-12'],
            ['full_name' => 'Bima Saputra', 'gender' => 'Laki-laki', 'division' => 'TECH', 'position' => 'SWE', 'status' => 'Kontrak', 'city' => 'Bandung', 'hire_months_ago' => 9, 'birth_date' => '1997-07-09'],
            ['full_name' => 'Citra Larasati', 'gender' => 'Perempuan', 'division' => 'OPS', 'position' => 'OPS-SPV', 'status' => 'Probation', 'city' => 'Surabaya', 'hire_months_ago' => 4, 'birth_date' => '1999-11-21'],
            ['full_name' => 'Dian Kusuma', 'gender' => 'Perempuan', 'division' => 'FIN', 'position' => 'ACC', 'status' => 'Tetap', 'city' => 'Jakarta', 'hire_months_ago' => 14, 'birth_date' => '1993-05-17'],
            ['full_name' => 'Eko Nugroho', 'gender' => 'Laki-laki', 'division' => 'OPS', 'position' => 'WHC', 'status' => 'Kontrak', 'city' => 'Bekasi', 'hire_months_ago' => 11, 'birth_date' => '1995-09-22'],
            ['full_name' => 'Fajar Maulana', 'gender' => 'Laki-laki', 'division' => 'TECH', 'position' => 'QA', 'status' => 'Tetap', 'city' => 'Depok', 'hire_months_ago' => 7, 'birth_date' => '1996-02-11'],
            ['full_name' => 'Gita Permata', 'gender' => 'Perempuan', 'division' => 'HR', 'position' => 'RCT', 'status' => 'Tetap', 'city' => 'Tangerang', 'hire_months_ago' => 13, 'birth_date' => '1998-01-30'],
            ['full_name' => 'Hendra Wijaya', 'gender' => 'Laki-laki', 'division' => 'SLS', 'position' => 'SE', 'status' => 'Kontrak', 'city' => 'Bogor', 'hire_months_ago' => 6, 'birth_date' => '1994-08-08'],
            ['full_name' => 'Intan Maharani', 'gender' => 'Perempuan', 'division' => 'CS', 'position' => 'CSS', 'status' => 'Probation', 'city' => 'Jakarta', 'hire_months_ago' => 3, 'birth_date' => '2000-04-05'],
            ['full_name' => 'Joko Santoso', 'gender' => 'Laki-laki', 'division' => 'FIN', 'position' => 'FNA', 'status' => 'Tetap', 'city' => 'Bandung', 'hire_months_ago' => 16, 'birth_date' => '1992-10-14'],
            ['full_name' => 'Kartika Ayu', 'gender' => 'Perempuan', 'division' => 'TECH', 'position' => 'PM', 'status' => 'Tetap', 'city' => 'Jakarta', 'hire_months_ago' => 10, 'birth_date' => '1991-12-02'],
            ['full_name' => 'Lukman Hakim', 'gender' => 'Laki-laki', 'division' => 'OPS', 'position' => 'OPS-SPV', 'status' => 'Tetap', 'city' => 'Semarang', 'hire_months_ago' => 20, 'birth_date' => '1989-06-29'],
            ['full_name' => 'Maya Sari', 'gender' => 'Perempuan', 'division' => 'HR', 'position' => 'PAY', 'status' => 'Kontrak', 'city' => 'Yogyakarta', 'hire_months_ago' => 8, 'birth_date' => '1997-03-27'],
            ['full_name' => 'Nanda Prakoso', 'gender' => 'Laki-laki', 'division' => 'TECH', 'position' => 'SWE', 'status' => 'Probation', 'city' => 'Surabaya', 'hire_months_ago' => 2, 'birth_date' => '2000-07-16'],
            ['full_name' => 'Ochi Lestari', 'gender' => 'Perempuan', 'division' => 'CS', 'position' => 'CSS', 'status' => 'Tetap', 'city' => 'Malang', 'hire_months_ago' => 12, 'birth_date' => '1996-11-08'],
            ['full_name' => 'Putra Ramadhan', 'gender' => 'Laki-laki', 'division' => 'SLS', 'position' => 'SE', 'status' => 'Tetap', 'city' => 'Medan', 'hire_months_ago' => 15, 'birth_date' => '1993-01-19'],
            ['full_name' => 'Qonita Zahra', 'gender' => 'Perempuan', 'division' => 'FIN', 'position' => 'ACC', 'status' => 'Kontrak', 'city' => 'Jakarta', 'hire_months_ago' => 5, 'birth_date' => '1998-05-23'],
            ['full_name' => 'Rizky Kurniawan', 'gender' => 'Laki-laki', 'division' => 'OPS', 'position' => 'WHC', 'status' => 'Tetap', 'city' => 'Cikarang', 'hire_months_ago' => 17, 'birth_date' => '1994-09-01'],
            ['full_name' => 'Salsa Amelia', 'gender' => 'Perempuan', 'division' => 'TECH', 'position' => 'QA', 'status' => 'Kontrak', 'city' => 'Bandung', 'hire_months_ago' => 4, 'birth_date' => '1999-02-13'],
            ['full_name' => 'Taufik Hidayat', 'gender' => 'Laki-laki', 'division' => 'OPS', 'position' => 'OPS-SPV', 'status' => 'Tetap', 'city' => 'Makassar', 'hire_months_ago' => 21, 'birth_date' => '1990-04-26'],
            ['full_name' => 'Uli Marpaung', 'gender' => 'Perempuan', 'division' => 'HR', 'position' => 'RCT', 'status' => 'Probation', 'city' => 'Batam', 'hire_months_ago' => 1, 'birth_date' => '2001-06-10'],
            ['full_name' => 'Vino Aditya', 'gender' => 'Laki-laki', 'division' => 'TECH', 'position' => 'PM', 'status' => 'Tetap', 'city' => 'Jakarta', 'hire_months_ago' => 19, 'birth_date' => '1992-12-18'],
            ['full_name' => 'Wulan Puspita', 'gender' => 'Perempuan', 'division' => 'CS', 'position' => 'CSS', 'status' => 'Kontrak', 'city' => 'Bandung', 'hire_months_ago' => 7, 'birth_date' => '1997-08-04'],
            ['full_name' => 'Yoga Prabowo', 'gender' => 'Laki-laki', 'division' => 'SLS', 'position' => 'SE', 'status' => 'Tetap', 'city' => 'Surakarta', 'hire_months_ago' => 9, 'birth_date' => '1995-10-12'],
        ];

        collect($employeeBlueprints)->values()->each(function (array $employeeData, int $index) use ($divisionMap, $positionMap, $statusMap): void {
            $employee = Employee::query()->create([
                'nik' => sprintf('EMP-%03d', $index + 1),
                'full_name' => $employeeData['full_name'],
                'email' => $this->generateEmail($employeeData['full_name'], $index),
                'phone' => sprintf('08%010d', 1111111111 + ($index * 137)),
                'gender' => $employeeData['gender'],
                'birth_date' => $employeeData['birth_date'],
                'hire_date' => now()->subMonths($employeeData['hire_months_ago'])->addDays(($index % 5) * 3)->toDateString(),
                'address' => $employeeData['city'],
                'division_id' => $divisionMap->get($employeeData['division'])->id,
                'position_id' => $positionMap->get($employeeData['position'])->id,
                'employment_status_id' => $statusMap->get($employeeData['status'])->id,
                'is_active' => true,
            ]);

            $this->seedFamiliesForEmployee($employee, $index);
            $this->seedDocumentsForEmployee($employee, $index);
        });
    }

    protected function generateEmail(string $fullName, int $index): string
    {
        $slug = str($fullName)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '.')
            ->trim('.');

        return sprintf('%s.%02d@example.com', $slug, $index + 1);
    }

    protected function seedFamiliesForEmployee(Employee $employee, int $index): void
    {
        $primaryPhone = preg_replace('/\D+/', '', (string) $employee->phone);
        $patterns = [
            [
                ['relationship' => 'Pasangan', 'age' => 2, 'dependent' => true],
            ],
            [
                ['relationship' => 'Pasangan', 'age' => 3, 'dependent' => true],
                ['relationship' => 'Anak', 'age' => 27, 'dependent' => true],
            ],
            [
                ['relationship' => 'Orang Tua', 'age' => -28, 'dependent' => false],
            ],
            [
                ['relationship' => 'Pasangan', 'age' => 1, 'dependent' => true],
                ['relationship' => 'Anak', 'age' => 29, 'dependent' => true],
                ['relationship' => 'Anak', 'age' => 33, 'dependent' => true],
            ],
            [
                ['relationship' => 'Saudara', 'age' => -3, 'dependent' => false],
                ['relationship' => 'Orang Tua', 'age' => -25, 'dependent' => false],
            ],
        ];

        $pattern = $patterns[$index % count($patterns)];

        foreach ($pattern as $memberIndex => $member) {
            EmployeeFamily::query()->create([
                'employee_id' => $employee->id,
                'name' => $this->buildFamilyMemberName($employee->full_name, $member['relationship'], $memberIndex),
                'relationship' => $member['relationship'],
                'birth_date' => $this->deriveFamilyBirthDate($employee, $member['relationship'], $member['age'], $memberIndex)->toDateString(),
                'phone' => $primaryPhone !== '' ? sprintf('08%010d', ((int) substr($primaryPhone, -10)) + $memberIndex + 11) : null,
                'is_dependent' => $member['dependent'],
                'notes' => $member['dependent'] ? 'Masuk tanggungan karyawan.' : 'Tidak masuk tanggungan utama.',
            ]);
        }
    }

    protected function seedDocumentsForEmployee(Employee $employee, int $index): void
    {
        $profiles = [
            [
                ['name' => 'KTP', 'type' => 'Identitas', 'status' => 'complete', 'issued_years_ago' => 6, 'expires_in_months' => 60],
                ['name' => 'NPWP', 'type' => 'Perpajakan', 'status' => 'complete', 'issued_years_ago' => 5, 'expires_in_months' => null],
                ['name' => 'BPJS Kesehatan', 'type' => 'Benefit', 'status' => 'complete', 'issued_years_ago' => 3, 'expires_in_months' => 24],
            ],
            [
                ['name' => 'KTP', 'type' => 'Identitas', 'status' => 'complete', 'issued_years_ago' => 7, 'expires_in_months' => 48],
                ['name' => 'Kontrak Kerja', 'type' => 'Kepegawaian', 'status' => 'expiring', 'issued_years_ago' => 1, 'expires_in_months' => 1],
                ['name' => 'BPJS Ketenagakerjaan', 'type' => 'Benefit', 'status' => 'complete', 'issued_years_ago' => 2, 'expires_in_months' => 18],
            ],
            [
                ['name' => 'KTP', 'type' => 'Identitas', 'status' => 'expired', 'issued_years_ago' => 8, 'expires_in_months' => -2],
                ['name' => 'Ijazah', 'type' => 'Pendidikan', 'status' => 'complete', 'issued_years_ago' => 10, 'expires_in_months' => null],
                ['name' => 'Sertifikat Kompetensi', 'type' => 'Sertifikasi', 'status' => 'expiring', 'issued_years_ago' => 2, 'expires_in_months' => 2],
            ],
            [
                ['name' => 'KTP', 'type' => 'Identitas', 'status' => 'complete', 'issued_years_ago' => 4, 'expires_in_months' => 36],
                ['name' => 'NPWP', 'type' => 'Perpajakan', 'status' => 'complete', 'issued_years_ago' => 4, 'expires_in_months' => null],
                ['name' => 'Kontrak Kerja', 'type' => 'Kepegawaian', 'status' => 'expired', 'issued_years_ago' => 2, 'expires_in_months' => -1],
                ['name' => 'BPJS Kesehatan', 'type' => 'Benefit', 'status' => 'complete', 'issued_years_ago' => 2, 'expires_in_months' => 12],
            ],
        ];

        $profile = $profiles[$index % count($profiles)];

        foreach ($profile as $documentIndex => $document) {
            $issuedAt = $employee->hire_date
                ->copy()
                ->subYears($document['issued_years_ago'])
                ->addDays($documentIndex * 14);

            $expiresAt = is_null($document['expires_in_months'])
                ? null
                : $issuedAt->copy()->addMonths($document['expires_in_months']);

            EmployeeDocument::query()->create([
                'employee_id' => $employee->id,
                'document_name' => $document['name'],
                'document_type' => $document['type'],
                'document_number' => sprintf('%s-%s-%02d', str($document['name'])->upper()->replace(' ', ''), $employee->nik, $documentIndex + 1),
                'issued_at' => $issuedAt->toDateString(),
                'expires_at' => $expiresAt?->toDateString(),
                'status' => $document['status'],
                'notes' => match ($document['status']) {
                    'expiring' => 'Perlu ditindaklanjuti dalam waktu dekat.',
                    'expired' => 'Perlu pembaruan dokumen.',
                    default => 'Dokumen lengkap dan aktif.',
                },
            ]);
        }
    }

    protected function buildFamilyMemberName(string $employeeName, string $relationship, int $memberIndex): string
    {
        $firstName = explode(' ', $employeeName)[0];

        return match ($relationship) {
            'Pasangan' => 'Pasangan ' . $firstName,
            'Anak' => 'Anak ' . $firstName . ' ' . ($memberIndex + 1),
            'Orang Tua' => 'Orang Tua ' . $firstName,
            'Saudara' => 'Saudara ' . $firstName,
            default => 'Keluarga ' . $firstName,
        };
    }

    protected function deriveFamilyBirthDate(Employee $employee, string $relationship, int $ageOffset, int $memberIndex): Carbon
    {
        $birthDate = $employee->birth_date->copy();

        return match ($relationship) {
            'Pasangan' => $birthDate->copy()->addYears($ageOffset)->addMonths($memberIndex),
            'Anak' => $birthDate->copy()->addYears($ageOffset)->addMonths($memberIndex * 7),
            'Orang Tua' => $birthDate->copy()->subYears(abs($ageOffset))->subMonths($memberIndex * 3),
            'Saudara' => $birthDate->copy()->addYears($ageOffset)->addMonths($memberIndex * 2),
            default => $birthDate->copy()->addYears($ageOffset),
        };
    }
}
