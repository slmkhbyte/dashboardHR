<?php

namespace App\Filament\Imports;

use App\Filament\Imports\Concerns\HasFlexibleDateImportColumns;
use App\Models\Employee;
use App\Models\EmployeeSapSnapshot;
use App\Models\EmployeeSapSnapshotRow;
use App\Support\EmployeeSapSnapshotComparer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Number;

class EmployeeSapSnapshotRowImporter extends Importer
{
    use HasFlexibleDateImportColumns;

    protected static ?string $model = EmployeeSapSnapshotRow::class;

    public static function getOptionsFormComponents(): array
    {
        return [
            Select::make('period_month')
                ->label('Bulan Snapshot')
                ->options(collect(range(1, 12))->mapWithKeys(
                    fn (int $month): array => [$month => now()->setMonth($month)->translatedFormat('F')],
                ))
                ->default((int) now()->format('n'))
                ->required(),
            TextInput::make('period_year')
                ->label('Tahun Snapshot')
                ->numeric()
                ->minValue(2000)
                ->maxValue(2100)
                ->default((int) now()->format('Y'))
                ->required(),
            Textarea::make('notes')
                ->label('Catatan')
                ->rows(2)
                ->columnSpanFull(),
        ];
    }

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nik_sap')
                ->label('NIK SAP')
                ->guess(['nik_sap', 'nik sap', 'nik'])
                ->requiredMapping()
                ->exampleHeader('NIK_SAP')
                ->example('13004844')
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
            ImportColumn::make('position')
                ->label('Jabatan')
                ->guess(['jabatan', 'position'])
                ->exampleHeader('JABATAN')
                ->example('HR Manager')
                ->rules(['nullable']),
            ImportColumn::make('employment_status')
                ->label('Status Karyawan')
                ->guess(['status', 'status karyawan', 'employment_status', 'employment status'])
                ->exampleHeader('STATUS')
                ->example('Tetap')
                ->rules(['nullable']),
            ImportColumn::make('employee_grade')
                ->label('Golongan Karyawan')
                ->guess(['golongan', 'golongan karyawan', 'grade', 'employee_grade', 'employee grade'])
                ->exampleHeader('Golongan')
                ->example('IB/13')
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
            static::dateImportColumn(ImportColumn::make('hire_date'))
                ->label('Tanggal Bergabung')
                ->guess(['tanggal bergabung', 'tgl bergabung', 'tmt bekerja', 'tmt kerja', 'hire_date', 'hire date'])
                ->exampleHeader('tmt Bekerja')
                ->example(now()->toDateString())
                ->rules(['nullable', 'date']),
            ImportColumn::make('is_active')
                ->label('Aktif')
                ->boolean()
                ->guess(['aktif', 'is_active', 'is active'])
                ->exampleHeader('is_active')
                ->example('true'),
        ];
    }

    public function resolveRecord(): EmployeeSapSnapshotRow
    {
        return EmployeeSapSnapshotRow::query()->firstOrNew([
            'employee_sap_snapshot_id' => $this->getSnapshot()->getKey(),
            'nik_sap' => $this->data['nik_sap'],
        ]);
    }

    protected function beforeSave(): void
    {
        $employee = Employee::query()->where('nik_sap', $this->data['nik_sap'])->first();

        $this->record->employee_sap_snapshot_id = $this->getSnapshot()->getKey();
        $this->record->employee_id = $employee?->getKey();
        $this->record->raw_data = $this->getOriginalData();
    }

    protected function afterSave(): void
    {
        app(EmployeeSapSnapshotComparer::class)->compareRow($this->record->refresh());
    }

    public function getValidationAttributes(): array
    {
        return [
            'nik_sap' => 'NIK SAP',
            'hire_date' => 'Tanggal Bergabung',
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import data SAP selesai dan ' . Number::format($import->successful_rows) . ' baris berhasil diproses.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' baris gagal diproses.';
        }

        return $body;
    }

    public function getJobConnection(): ?string
    {
        return app()->isLocal() ? 'sync' : parent::getJobConnection();
    }

    private function getSnapshot(): EmployeeSapSnapshot
    {
        $options = $this->getOptions();

        return EmployeeSapSnapshot::query()->firstOrCreate(
            [
                'period_month' => (int) ($options['period_month'] ?? now()->format('n')),
                'period_year' => (int) ($options['period_year'] ?? now()->format('Y')),
            ],
            [
                'source_file_name' => $this->getImport()->file_name,
                'notes' => $options['notes'] ?? null,
                'import_id' => $this->getImport()->getKey(),
                'imported_by' => $this->getImport()->user_id,
                'imported_at' => now(),
            ],
        );
    }

    private static function normalizeNikSap(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $value = preg_replace('/\s+/', '', trim((string) $value));

        if (preg_match('/^\d+\.0+$/', $value)) {
            $value = strstr($value, '.', before_needle: true);
        }

        return $value;
    }
}
