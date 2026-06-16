<?php

namespace App\Filament\Imports;

use App\Models\EmployeeSapSnapshot;
use App\Models\EmployeeSapSnapshotRow;
use App\Services\EmployeeSap\CompleteEmployeeSapSnapshotImportService;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Select;
use Illuminate\Support\Number;

class EmployeeSapSnapshotRowImporter extends Importer
{
    protected static ?string $model = EmployeeSapSnapshotRow::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nik_sap')
                ->label('NIK SAP')
                ->guess(['nik_sap', 'nik sap', 'nik'])
                ->requiredMapping()
                ->exampleHeader('NIK_SAP')
                ->example('13004844')
                ->castStateUsing(fn (mixed $originalState, mixed $state): mixed => self::normalizeText($originalState ?? $state))
                ->rules(['required']),
            ImportColumn::make('name')
                ->label('Nama')
                ->guess(['nama', 'name', 'full_name', 'nama karyawan'])
                ->exampleHeader('Nama')
                ->rules(['nullable']),
            ImportColumn::make('position')
                ->label('Jabatan')
                ->guess(['jabatan', 'position'])
                ->exampleHeader('Jabatan')
                ->rules(['nullable']),
            ImportColumn::make('work_unit')
                ->label('Work Unit')
                ->guess(['bagian', 'work unit', 'work_unit', 'unit kerja'])
                ->exampleHeader('Bagian')
                ->rules(['nullable']),
            ImportColumn::make('lvl_bod')
                ->label('LVL BOD')
                ->numeric()
                ->guess(['lvl bod', 'level bod', 'lvl_bod'])
                ->exampleHeader('LVL BOD')
                ->rules(['nullable', 'integer']),
            ImportColumn::make('employee_grade')
                ->label('Golongan')
                ->guess(['golongan', 'grade', 'employee_grade'])
                ->exampleHeader('Golongan')
                ->rules(['nullable']),
            ImportColumn::make('employment_status')
                ->label('Status Karyawan')
                ->guess(['status', 'status karyawan', 'employment_status'])
                ->exampleHeader('Status')
                ->rules(['nullable']),
            ImportColumn::make('company')
                ->label('Company/Subsidiary')
                ->guess(['company', 'subsidiary', 'perusahaan'])
                ->rules(['nullable']),
            ImportColumn::make('department')
                ->label('Department')
                ->guess(['department', 'departemen'])
                ->rules(['nullable']),
            ImportColumn::make('division')
                ->label('Division')
                ->guess(['division', 'divisi'])
                ->rules(['nullable']),
            ImportColumn::make('unit')
                ->label('Unit')
                ->guess(['unit'])
                ->rules(['nullable']),
            ImportColumn::make('location')
                ->label('Location')
                ->guess(['location', 'lokasi'])
                ->rules(['nullable']),
            ImportColumn::make('superior')
                ->label('Superior')
                ->guess(['superior', 'atasan'])
                ->rules(['nullable']),
        ];
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            Select::make('period_month')
                ->label('Bulan')
                ->options([
                    1 => 'Januari',
                    2 => 'Februari',
                    3 => 'Maret',
                    4 => 'April',
                    5 => 'Mei',
                    6 => 'Juni',
                    7 => 'Juli',
                    8 => 'Agustus',
                    9 => 'September',
                    10 => 'Oktober',
                    11 => 'November',
                    12 => 'Desember',
                ])
                ->default(now()->month)
                ->required(),
            Select::make('period_year')
                ->label('Tahun')
                ->options(collect(range((int) now()->year - 5, (int) now()->year + 1))->mapWithKeys(fn (int $year): array => [$year => (string) $year])->all())
                ->default(now()->year)
                ->required(),
        ];
    }

    public function resolveRecord(): EmployeeSapSnapshotRow
    {
        return EmployeeSapSnapshotRow::query()->firstOrNew([
            'snapshot_id' => $this->snapshot()->getKey(),
            'nik_sap' => $this->data['nik_sap'],
        ]);
    }

    protected function beforeSave(): void
    {
        $this->record->snapshot()->associate($this->snapshot());
        $this->record->raw_data = $this->data;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $snapshot = EmployeeSapSnapshot::query()
            ->where('import_id', $import->getKey())
            ->first();

        if ($snapshot) {
            app(CompleteEmployeeSapSnapshotImportService::class)->complete($snapshot);
        }

        $body = 'SAP snapshot import completed with ' . Number::format($import->successful_rows) . ' successful ' . str('row')->plural($import->successful_rows) . '.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    private function snapshot(): EmployeeSapSnapshot
    {
        $options = $this->options ?? [];

        return EmployeeSapSnapshot::query()->firstOrCreate(
            ['import_id' => $this->import->getKey()],
            [
                'period_month' => (int) ($options['period_month'] ?? now()->month),
                'period_year' => (int) ($options['period_year'] ?? now()->year),
                'source_file_name' => $this->import->file_name,
                'imported_by' => $this->import->user_id,
            ],
        );
    }

    private static function normalizeText(mixed $value): ?string
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
