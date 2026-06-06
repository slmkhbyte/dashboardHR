<?php

namespace Database\Seeders;

use App\Models\EmploymentStatus;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminAndMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        EmploymentStatus::getOrCreateDefault();
        Position::getOrCreateDefault();

        collect([
            ['name' => 'Tetap', 'color' => 'success', 'description' => 'Karyawan permanen'],
            ['name' => 'Kontrak', 'color' => 'warning', 'description' => 'Karyawan kontrak'],
            ['name' => 'Probation', 'color' => 'info', 'description' => 'Masa percobaan'],
        ])->each(fn (array $status) => EmploymentStatus::query()->firstOrCreate(
            ['name' => $status['name']],
            $status + ['is_active' => true],
        ));

        collect([
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
        ])->each(fn (array $position) => Position::query()->firstOrCreate(
            ['name' => $position['name']],
            $position + ['is_active' => true],
        ));

        User::query()->updateOrCreate([
            'email' => 'salma@dbgunme.com',
        ], [
            'name' => 'Salma Khairunnisa',
            'password' => Hash::make('dbgunmekeren'),
        ]);
    }
}
