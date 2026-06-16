<?php

namespace App\Services\EmployeeSap;

use App\Models\Employee;

class EmployeeSapFieldMap
{
    /**
     * @return array<string, array{label: string, sap: string, local: callable(Employee): mixed, history?: string}>
     */
    public static function comparableFields(): array
    {
        return [
            'name' => [
                'label' => 'Nama',
                'sap' => 'name',
                'local' => fn (Employee $employee): ?string => $employee->full_name,
                'history' => 'full_name',
            ],
            'position' => [
                'label' => 'Jabatan',
                'sap' => 'position',
                'local' => fn (Employee $employee): ?string => $employee->position?->name,
                'history' => 'position_id',
            ],
            'work_unit' => [
                'label' => 'Work Unit',
                'sap' => 'work_unit',
                'local' => fn (Employee $employee): ?string => $employee->work_unit,
                'history' => 'work_unit',
            ],
            'company' => [
                'label' => 'Company/Subsidiary',
                'sap' => 'company',
                'local' => fn (Employee $employee): ?string => $employee->company,
                'history' => 'company',
            ],
            'department' => [
                'label' => 'Department',
                'sap' => 'department',
                'local' => fn (Employee $employee): ?string => $employee->department,
                'history' => 'department',
            ],
            'division' => [
                'label' => 'Division',
                'sap' => 'division',
                'local' => fn (Employee $employee): ?string => $employee->division,
                'history' => 'division',
            ],
            'unit' => [
                'label' => 'Unit',
                'sap' => 'unit',
                'local' => fn (Employee $employee): ?string => $employee->unit,
                'history' => 'unit',
            ],
            'location' => [
                'label' => 'Location',
                'sap' => 'location',
                'local' => fn (Employee $employee): ?string => $employee->location,
                'history' => 'location',
            ],
            'superior' => [
                'label' => 'Superior',
                'sap' => 'superior',
                'local' => fn (Employee $employee): ?string => $employee->superior,
                'history' => 'superior',
            ],
            'lvl_bod' => [
                'label' => 'LVL BOD',
                'sap' => 'lvl_bod',
                'local' => fn (Employee $employee): mixed => $employee->lvl_bod,
                'history' => 'lvl_bod',
            ],
            'employee_grade' => [
                'label' => 'Golongan',
                'sap' => 'employee_grade',
                'local' => fn (Employee $employee): ?string => $employee->employee_grade,
                'history' => 'employee_grade',
            ],
            'employment_status' => [
                'label' => 'Status Karyawan',
                'sap' => 'employment_status',
                'local' => fn (Employee $employee): ?string => $employee->employmentStatus?->name,
                'history' => 'employment_status_id',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function jobTrackedEmployeeFields(): array
    {
        return [
            'position_id' => 'Jabatan',
            'employment_status_id' => 'Status Karyawan',
            'employee_grade' => 'Golongan',
            'work_unit' => 'Work Unit',
            'company' => 'Company/Subsidiary',
            'department' => 'Department',
            'division' => 'Division',
            'unit' => 'Unit',
            'location' => 'Location',
            'superior' => 'Superior',
            'lvl_bod' => 'LVL BOD',
            'is_active' => 'Status Aktif',
        ];
    }

    public static function normalize(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        $value = trim((string) $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return $value === '' ? null : mb_strtolower($value);
    }

    public static function display(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }
}
