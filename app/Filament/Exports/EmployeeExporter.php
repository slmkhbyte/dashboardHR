<?php

namespace App\Filament\Exports;

use App\Models\Employee;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class EmployeeExporter extends Exporter
{
    protected static ?string $model = Employee::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('nik_sap')->label('NIK SAP'),
            ExportColumn::make('nik_karyawan')->label('NIK Karyawan'),
            ExportColumn::make('full_name')->label('Nama Lengkap'),
            ExportColumn::make('email')->label('Email'),
            ExportColumn::make('phone')->label('Telepon'),
            ExportColumn::make('gender')->label('Jenis Kelamin'),
            ExportColumn::make('birth_place')->label('Tempat Lahir'),
            ExportColumn::make('birth_date')->label('Tanggal Lahir'),
            ExportColumn::make('last_education')->label('Pendidikan Terakhir'),
            ExportColumn::make('hire_date')->label('Tanggal Bergabung'),
            ExportColumn::make('work_unit')->label('Work Unit'),
            ExportColumn::make('position.name')->label('Jabatan'),
            ExportColumn::make('employmentStatus.name')->label('Status Karyawan'),
            ExportColumn::make('employee_grade')->label('Golongan Karyawan'),
            ExportColumn::make('religion')->label('Agama'),
            ExportColumn::make('dependent_code')->label('Tanggungan'),
            ExportColumn::make('lvl_bod')->label('LVL BOD'),
            ExportColumn::make('award_20_years')
                ->label('Penghargaan 20 Tahun')
                ->formatStateUsing(fn (Employee $record): ?string => $record->awardDateForYears(20)?->toDateString()),
            ExportColumn::make('award_25_years')
                ->label('Penghargaan 25 Tahun')
                ->formatStateUsing(fn (Employee $record): ?string => $record->awardDateForYears(25)?->toDateString()),
            ExportColumn::make('award_30_years')
                ->label('Penghargaan 30 Tahun')
                ->formatStateUsing(fn (Employee $record): ?string => $record->awardDateForYears(30)?->toDateString()),
            ExportColumn::make('award_35_years')
                ->label('Penghargaan 35 Tahun')
                ->formatStateUsing(fn (Employee $record): ?string => $record->awardDateForYears(35)?->toDateString()),
            ExportColumn::make('is_active')
                ->label('Aktif')
                ->formatStateUsing(fn (bool $state): string => $state ? 'true' : 'false'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your employee export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
