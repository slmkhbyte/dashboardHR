<?php

namespace App\Filament\Imports;

use App\Filament\Imports\Concerns\HasFlexibleDateImportColumns;
use App\Models\Employee;
use App\Models\EmployeeFamily;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class EmployeeFamilyImporter extends Importer
{
    use HasFlexibleDateImportColumns;

    protected static ?string $model = EmployeeFamily::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('employee')
                ->label('NIK Karyawan')
                ->relationship('employee', 'nik')
                ->requiredMapping()
                ->exampleHeader('employee_nik')
                ->example('EMP-100')
                ->fillRecordUsing(function (EmployeeFamily $record, ?string $state): void {
                    if (blank($state)) {
                        return;
                    }

                    $record->employee()->associate(
                        Employee::query()->where('nik', $state)->firstOrFail()
                    );
                }),
            ImportColumn::make('name')
                ->label('Nama Keluarga')
                ->requiredMapping()
                ->exampleHeader('family_name')
                ->example('Nama Pasangan')
                ->rules(['required', 'max:255']),
            ImportColumn::make('relationship')
                ->label('Hubungan')
                ->requiredMapping()
                ->exampleHeader('relationship')
                ->example('Pasangan')
                ->rules(['required', 'max:100']),
            static::dateImportColumn(ImportColumn::make('birth_date'))
                ->label('Tanggal Lahir')
                ->exampleHeader('birth_date')
                ->example('1995-04-10')
                ->rules(['nullable', 'date']),
            ImportColumn::make('phone')
                ->label('Telepon')
                ->exampleHeader('phone')
                ->example('08123456789')
                ->rules(['nullable', 'max:50']),
            ImportColumn::make('is_dependent')
                ->label('Tanggungan')
                ->boolean()
                ->exampleHeader('is_dependent')
                ->example('true'),
            ImportColumn::make('notes')
                ->label('Catatan')
                ->exampleHeader('notes')
                ->example('Catatan tambahan'),
        ];
    }

    public function resolveRecord(): EmployeeFamily
    {
        return new EmployeeFamily();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your employee family import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
