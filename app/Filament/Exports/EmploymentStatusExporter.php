<?php

namespace App\Filament\Exports;

use App\Models\EmploymentStatus;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class EmploymentStatusExporter extends Exporter
{
    protected static ?string $model = EmploymentStatus::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')->label('Status'),
            ExportColumn::make('color')->label('Warna Badge'),
            ExportColumn::make('description')->label('Deskripsi'),
            ExportColumn::make('employees_count')->counts('employees')->label('Jumlah Karyawan'),
            ExportColumn::make('is_active')
                ->label('Aktif')
                ->formatStateUsing(fn (bool $state): string => $state ? 'true' : 'false'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your employment status export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
