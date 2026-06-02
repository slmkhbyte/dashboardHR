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
            ImportColumn::make('nik_sap')
                ->label('NIK SAP')
                ->guess(['nik_sap', 'nik sap', 'nik'])
                ->requiredMapping()
                ->exampleHeader('NIK_SAP')
                ->example('13004844')
                ->helperText('Hanya NIK SAP yang wajib. Baris tanpa NIK SAP akan ditandai sebagai gagal.')
                ->castStateUsing(fn (mixed $originalState, mixed $state): mixed => self::normalizeNikSap($originalState ?? $state))
                ->rules(['required', 'digits:8']),
            ImportColumn::make('nik_karyawan')
                ->label('NIK Karyawan')
                ->guess(['nik karyawan', 'nik_karyawan', 'nik'])
                ->exampleHeader('nik')
                ->example('000.0194.0573.0337')
                ->rules(['nullable']),
            ImportColumn::make('full_name')
                ->label('Nama Lengkap')
                ->guess(['nama lengkap', 'nama karyawan', 'nama', 'full_name', 'full name'])
                ->exampleHeader('nama')
                ->example('Nama Karyawan')
                ->rules(['nullable']),
            ImportColumn::make('gender')
                ->label('Jenis Kelamin')
                ->guess(['gender', 'jenis kelamin'])
                ->exampleHeader('Gender')
                ->example('P')
                ->rules(['nullable'])
                ->fillRecordUsing(function (Employee $record, ?string $state): void {
                    if (blank($state)) {
                        return;
                    }

                    $record->gender = match (strtoupper(trim($state))) {
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                        default => trim($state),
                    };
                }),
            ImportColumn::make('religion')
                ->label('Agama')
                ->guess(['agama', 'religion'])
                ->exampleHeader('agama')
                ->example('Islam')
                ->rules(['nullable']),
            ImportColumn::make('birth_place')
                ->label('Tempat Lahir')
                ->guess(['tempat lahir', 'birth_place', 'birth place'])
                ->exampleHeader('Tempat Lahir')
                ->example('Jakarta')
                ->rules(['nullable']),
            static::dateImportColumn(ImportColumn::make('birth_date'))
                ->label('Tanggal Lahir')
                ->guess(['tanggal lahir', 'tgl lahir', 'birth_date', 'birth date'])
                ->exampleHeader('Tgl Lahir')
                ->example('1994-03-12')
                ->rules(['nullable', 'date']),
            ImportColumn::make('last_education')
                ->label('Pendidikan Terakhir')
                ->guess(['pendidikan terakhir', 'pendidikan', 'last_education', 'last education'])
                ->exampleHeader('Pendidikan terakhir')
                ->example('S1')
                ->rules(['nullable']),
            static::dateImportColumn(ImportColumn::make('hire_date'))
                ->label('Tanggal Bergabung')
                ->guess(['tanggal bergabung', 'tgl bergabung', 'tmt bekerja', 'tmt kerja', 'hire_date', 'hire date'])
                ->exampleHeader('tmt Bekerja')
                ->example(now()->toDateString())
                ->rules(['nullable', 'date']),
            ImportColumn::make('email')
                ->label('Email')
                ->guess(['email', 'e-mail'])
                ->exampleHeader('Email')
                ->example('nama@example.com')
                ->rules(['nullable']),
            ImportColumn::make('phone')
                ->label('Telepon')
                ->guess(['telepon', 'telp', 'no telp', 'phone'])
                ->exampleHeader('no telp')
                ->example('08123456789')
                ->rules(['nullable']),
            ImportColumn::make('address')
                ->label('Alamat')
                ->guess(['alamat', 'address'])
                ->exampleHeader('address')
                ->example('Jakarta')
                ->rules(['nullable']),
            ImportColumn::make('position')
                ->label('Jabatan')
                ->guess(['jabatan', 'position'])
                ->exampleHeader('JABATAN')
                ->example('HR Manager')
                ->rules(['nullable'])
                ->fillRecordUsing(function (Employee $record, ?string $state): void {
                    if (blank($state)) {
                        return;
                    }

                    $record->position()->associate(Position::query()->firstOrCreate(
                        ['name' => trim($state)],
                        ['is_active' => true],
                    ));
                }),
            ImportColumn::make('employment_status')
                ->label('Status Karyawan')
                ->guess(['status', 'status karyawan', 'employment_status', 'employment status'])
                ->exampleHeader('STATUS')
                ->example('Tetap')
                ->rules(['nullable'])
                ->fillRecordUsing(function (Employee $record, ?string $state): void {
                    if (blank($state)) {
                        return;
                    }

                    $record->employmentStatus()->associate(EmploymentStatus::query()->firstOrCreate(
                        ['name' => trim($state)],
                        ['color' => 'info', 'is_active' => true],
                    ));
                }),
            ImportColumn::make('employee_grade')
                ->label('Golongan Karyawan')
                ->guess(['golongan', 'golongan karyawan', 'grade', 'employee_grade', 'employee grade'])
                ->exampleHeader('Golongan')
                ->example('IB/13')
                ->rules(['nullable']),
            ImportColumn::make('marital_status')
                ->label('Status Tanggungan')
                ->guess(['tanggungan', 'status tanggungan', 'marital_status', 'marital status'])
                ->exampleHeader('Tanggungan')
                ->example('K/0')
                ->rules(['nullable'])
                ->fillRecordUsing(function (Employee $record, ?string $state): void {
                    if (blank($state)) {
                        return;
                    }

                    if (str_contains($state, '/')) {
                        [$maritalStatus, $dependentCount] = explode('/', $state, 2);
                        $record->marital_status = strtoupper(trim($maritalStatus));
                        if (is_numeric($dependentCount)) {
                            $record->dependent_count = (int) trim($dependentCount);
                        }

                        return;
                    }

                    $record->marital_status = strtoupper(trim($state));
                }),
            ImportColumn::make('dependent_count')
                ->label('Jumlah Tanggungan Anak')
                ->numeric()
                ->guess(['jumlah tanggungan anak', 'jumlah anak', 'dependent_count', 'dependent count'])
                ->exampleHeader('dependent_count')
                ->example('0')
                ->rules(['nullable']),
            ImportColumn::make('work_unit')
                ->label('Work Unit')
                ->guess(['bagian', 'work unit', 'work_unit', 'unit kerja'])
                ->exampleHeader('BAGIAN')
                ->example('AFDELING I')
                ->rules(['nullable']),
            ImportColumn::make('lvl_bod')
                ->label('LVL BOD')
                ->numeric()
                ->guess(['lvl bod', 'level bod', 'lvl_bod'])
                ->exampleHeader('LVL BOD')
                ->example('6')
                ->rules(['nullable']),
            ImportColumn::make('is_active')
                ->label('Aktif')
                ->boolean()
                ->guess(['aktif', 'is_active', 'is active'])
                ->exampleHeader('is_active')
                ->example('true'),
        ];
    }

    public function resolveRecord(): Employee
    {
        return Employee::query()->firstOrNew([
            'nik_sap' => $this->data['nik_sap'],
        ]);
    }

    protected function beforeSave(): void
    {
        $this->record->full_name ??= 'Karyawan ' . $this->record->nik_sap;
        $this->record->hire_date ??= now()->toDateString();

        if (blank($this->record->position_id)) {
            $this->record->position()->associate(Position::query()->firstOrCreate(
                ['name' => 'Belum Diisi'],
                ['is_active' => true],
            ));
        }

        if (blank($this->record->employment_status_id)) {
            $this->record->employmentStatus()->associate(EmploymentStatus::query()->firstOrCreate(
                ['name' => 'Belum Diisi'],
                ['color' => 'gray', 'is_active' => true],
            ));
        }
    }

    public function getValidationAttributes(): array
    {
        return [
            'nik_sap' => 'NIK SAP',
            'birth_date' => 'Tanggal Lahir',
            'hire_date' => 'Tanggal Bergabung',
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your employee import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

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
}
