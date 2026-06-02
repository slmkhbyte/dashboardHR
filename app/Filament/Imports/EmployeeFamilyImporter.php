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
                ->label('NIK SAP')
                ->relationship('employee', 'nik_sap')
                ->guess(['nik_sap', 'nik sap', 'nik'])
                ->requiredMapping()
                ->exampleHeader('NIK_SAP')
                ->example('13004844')
                ->castStateUsing(fn (mixed $originalState, mixed $state): mixed => self::normalizeNikSap($originalState ?? $state))
                ->rules(['required', 'exists:employees,nik_sap'])
                ->fillRecordUsing(function (EmployeeFamily $record, ?string $state): void {
                    $state = self::normalizeNikSap($state);

                    if (blank($state)) {
                        return;
                    }

                    $record->employee()->associate(
                        Employee::query()->where('nik_sap', $state)->firstOrFail()
                    );
                }),
            ImportColumn::make('name')
                ->label('Nama Keluarga')
                ->guess(['nama anggota keluarga', 'nama keluarga', 'family_name', 'name'])
                ->requiredMapping()
                ->exampleHeader('Nama anggota keluarga')
                ->example('Siti Ramdawati')
                ->rules(['required', 'max:255']),
            ImportColumn::make('relationship')
                ->label('Hubungan')
                ->guess(['status', 'hubungan', 'relationship'])
                ->requiredMapping()
                ->exampleHeader('Status')
                ->example('Istri')
                ->castStateUsing(fn (mixed $originalState, mixed $state): mixed => self::normalizeRelationship($originalState ?? $state))
                ->rules(['required', 'max:100']),
            ImportColumn::make('gender')
                ->label('Gender')
                ->guess(['gender', 'jenis kelamin'])
                ->exampleHeader('Gender')
                ->example('P:')
                ->castStateUsing(fn (mixed $originalState, mixed $state): mixed => self::normalizeGender($originalState ?? $state))
                ->rules(['nullable', 'max:20'])
                ->ignoreBlankState(),
            ImportColumn::make('birth_place')
                ->label('Tempat Lahir')
                ->guess(['tempat lahir', 'birth_place', 'birth place'])
                ->exampleHeader('Tempat lahir')
                ->example('Putussibau')
                ->rules(['nullable', 'max:255'])
                ->ignoreBlankState(),
            static::dateImportColumn(ImportColumn::make('birth_date'))
                ->label('Tanggal Lahir')
                ->guess(['tanggal lahir', 'birth_date', 'birth date'])
                ->exampleHeader('Tanggal Lahir')
                ->example('13/04/1990')
                ->rules(['nullable', 'date']),
            ImportColumn::make('last_education')
                ->label('Pendidikan Terakhir')
                ->guess(['pendidikan terakhir', 'last_education', 'last education', 'pendidikan'])
                ->exampleHeader('Pendidikan Terakhir')
                ->example('SMP')
                ->rules(['nullable', 'max:100'])
                ->ignoreBlankState(),
            ImportColumn::make('religion')
                ->label('Agama')
                ->guess(['agama', 'religion'])
                ->exampleHeader('Agama')
                ->example('Islam')
                ->rules(['nullable', 'max:100'])
                ->ignoreBlankState(),
            ImportColumn::make('ethnicity')
                ->label('Suku')
                ->guess(['suku', 'ethnicity'])
                ->exampleHeader('Suku')
                ->example('Melayu')
                ->rules(['nullable', 'max:100'])
                ->ignoreBlankState(),
            ImportColumn::make('address')
                ->label('Alamat')
                ->guess(['alamat', 'address'])
                ->exampleHeader('Alamat')
                ->example('Alamat keluarga')
                ->ignoreBlankState(),
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

    public function getValidationMessages(): array
    {
        return [
            'employee.exists' => 'NIK SAP tidak ditemukan di data karyawan utama.',
        ];
    }

    public function getValidationAttributes(): array
    {
        return [
            'employee' => 'NIK SAP',
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your employee family import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    private static function normalizeNikSap(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $value = trim((string) $value);
        $value = preg_replace('/\s+/', '', $value);

        if (preg_match('/^\d+\.0+$/', $value)) {
            $value = strstr($value, '.', before_needle: true);
        }

        return $value;
    }

    private static function normalizeRelationship(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $value = trim($value);
        $normalized = strtolower($value);

        return match ($normalized) {
            'orang tua', 'orangtua' => 'Orang Tua',
            default => str($value)->title()->toString(),
        };
    }

    private static function normalizeGender(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $value = strtoupper(trim($value));
        $value = trim($value, " \t\n\r\0\x0B:.;");

        return match ($value) {
            'P', 'PEREMPUAN', 'WANITA' => 'P',
            'L', 'LAKI-LAKI', 'LAKI LAKI', 'PRIA' => 'L',
            default => $value,
        };
    }
}
