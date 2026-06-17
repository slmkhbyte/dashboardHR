<?php

namespace App\Support;

use App\Models\Employee;
use App\Models\EmployeeSapSnapshotRow;
use DateTimeInterface;

class EmployeeSapFieldMap
{
    public static function trackedFields(): array
    {
        return [
            'position' => 'Jabatan',
            'employment_status' => 'Status Karyawan',
            'employee_grade' => 'Golongan',
            'work_unit' => 'Work Unit',
            'lvl_bod' => 'LVL BOD',
            'hire_date' => 'Tanggal Bergabung',
        ];
    }

    public static function employeeHistoryAttributes(): array
    {
        return [
            'position_id' => 'Jabatan',
            'employment_status_id' => 'Status Karyawan',
            'employee_grade' => 'Golongan',
            'work_unit' => 'Work Unit',
            'lvl_bod' => 'LVL BOD',
            'hire_date' => 'Tanggal Bergabung',
            'is_active' => 'Aktif',
        ];
    }

    public static function employeeValue(Employee $employee, string $field): mixed
    {
        return match ($field) {
            'position' => $employee->position?->name,
            'employment_status' => $employee->employmentStatus?->name,
            default => $employee->{$field},
        };
    }

    public static function sapValue(EmployeeSapSnapshotRow $row, string $field): mixed
    {
        return $row->{$field};
    }

    public static function normalize(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return preg_replace('/\s+/', ' ', trim((string) $value));
    }

    public static function display(mixed $value): ?string
    {
        return self::normalize($value);
    }
}
