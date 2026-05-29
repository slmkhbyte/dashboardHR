<?php

namespace App\Filament\Imports;

use App\Filament\Imports\Concerns\HasFlexibleDateImportColumns;
use App\Models\Division;
use App\Models\Employee;
use App\Models\EmploymentStatus;
use App\Models\Position;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class EmployeeImporter extends Importer
{
    use HasFlexibleDateImportColumns;

    protected static ?string $model = Employee::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nik')
                ->label('NIK')
                ->requiredMapping()
                ->exampleHeader('nik')
                ->example('EMP-100')
                ->rules(['required', 'max:100']),
            ImportColumn::make('full_name')
                ->label('Nama Lengkap')
                ->requiredMapping()
                ->exampleHeader('full_name')
                ->example('Nama Karyawan')
                ->rules(['required', 'max:255']),
            ImportColumn::make('email')
                ->label('Email')
                ->exampleHeader('email')
                ->example('nama@example.com')
                ->rules(['nullable', 'email']),
            ImportColumn::make('phone')
                ->label('Telepon')
                ->exampleHeader('phone')
                ->example('08123456789')
                ->rules(['nullable', 'max:50']),
            ImportColumn::make('gender')
                ->label('Jenis Kelamin')
                ->exampleHeader('gender')
                ->example('Perempuan')
                ->rules(['nullable', 'max:50']),
            static::dateImportColumn(ImportColumn::make('birth_date'))
                ->label('Tanggal Lahir')
                ->exampleHeader('birth_date')
                ->example('1994-03-12')
                ->rules(['nullable', 'date']),
            static::dateImportColumn(ImportColumn::make('hire_date'))
                ->label('Tanggal Bergabung')
                ->requiredMapping()
                ->exampleHeader('hire_date')
                ->example(now()->toDateString())
                ->rules(['required', 'date']),
            ImportColumn::make('address')
                ->label('Alamat')
                ->exampleHeader('address')
                ->example('Jakarta')
                ->rules(['nullable']),
            ImportColumn::make('is_active')
                ->label('Aktif')
                ->boolean()
                ->exampleHeader('is_active')
                ->example('true'),
            ImportColumn::make('division')
                ->label('Divisi')
                ->relationship('division', 'name')
                ->requiredMapping()
                ->exampleHeader('division')
                ->example('Human Resources')
                ->fillRecordUsing(function (Employee $record, ?string $state): void {
                    if (blank($state)) {
                        return;
                    }

                    $record->division()->associate(Division::query()->firstOrCreate(
                        ['name' => $state],
                        ['is_active' => true],
                    ));
                }),
            ImportColumn::make('position')
                ->label('Jabatan')
                ->relationship('position', 'name')
                ->requiredMapping()
                ->exampleHeader('position')
                ->example('HR Manager')
                ->fillRecordUsing(function (Employee $record, ?string $state): void {
                    if (blank($state)) {
                        return;
                    }

                    $record->position()->associate(Position::query()->firstOrCreate(
                        ['name' => $state],
                        ['is_active' => true],
                    ));
                }),
            ImportColumn::make('employment_status')
                ->label('Status Kerja')
                ->relationship('employmentStatus', 'name')
                ->requiredMapping()
                ->exampleHeader('employment_status')
                ->example('Tetap')
                ->fillRecordUsing(function (Employee $record, ?string $state): void {
                    if (blank($state)) {
                        return;
                    }

                    $record->employmentStatus()->associate(EmploymentStatus::query()->firstOrCreate(
                        ['name' => $state],
                        ['color' => 'info', 'is_active' => true],
                    ));
                }),
        ];
    }

    public function resolveRecord(): Employee
    {
        return Employee::query()->firstOrNew([
            'nik' => $this->data['nik'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your employee import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
