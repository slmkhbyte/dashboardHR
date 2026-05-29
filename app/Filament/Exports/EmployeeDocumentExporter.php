<?php

namespace App\Filament\Exports;

use App\Models\EmployeeDocument;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class EmployeeDocumentExporter extends Exporter
{
    protected static ?string $model = EmployeeDocument::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('employee.nik')->label('NIK Karyawan'),
            ExportColumn::make('employee.full_name')->label('Nama Karyawan'),
            ExportColumn::make('document_name')->label('Nama Dokumen'),
            ExportColumn::make('document_type')->label('Jenis Dokumen'),
            ExportColumn::make('document_number')->label('Nomor Dokumen'),
            ExportColumn::make('issued_at')->label('Tanggal Terbit'),
            ExportColumn::make('expires_at')->label('Tanggal Kedaluwarsa'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('notes')->label('Catatan'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your employee document export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
