<?php

namespace App\Filament\Exports;

use App\Models\EmployeeFamily;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class EmployeeFamilyExporter extends Exporter
{
    protected static ?string $model = EmployeeFamily::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('employee.nik')->label('NIK Karyawan'),
            ExportColumn::make('employee.full_name')->label('Nama Karyawan'),
            ExportColumn::make('name')->label('Nama Keluarga'),
            ExportColumn::make('relationship')->label('Hubungan'),
            ExportColumn::make('birth_date')->label('Tanggal Lahir'),
            ExportColumn::make('phone')->label('Telepon'),
            ExportColumn::make('is_dependent')
                ->label('Tanggungan')
                ->formatStateUsing(fn (bool $state): string => $state ? 'true' : 'false'),
            ExportColumn::make('notes')->label('Catatan'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your employee family export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
