<?php

namespace App\Filament\Resources\EmployeeSapSnapshots;

use App\Filament\Imports\EmployeeSapSnapshotRowImporter;
use App\Filament\Resources\EmployeeSapSnapshots\Pages\ListEmployeeSapSnapshots;
use App\Filament\Resources\EmployeeSapSnapshots\Pages\ViewEmployeeSapSnapshot;
use App\Filament\Resources\EmployeeSapSnapshots\RelationManagers\DifferencesRelationManager;
use App\Filament\Resources\EmployeeSapSnapshots\RelationManagers\RowsRelationManager;
use App\Models\EmployeeSapSnapshot;
use BackedEnum;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmployeeSapSnapshotResource extends Resource
{
    protected static ?string $model = EmployeeSapSnapshot::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentMagnifyingGlass;

    protected static string | \UnitEnum | null $navigationGroup = 'Operasional HR';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Karyawan SAP';

    protected static ?string $pluralModelLabel = 'Karyawan SAP';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('period')
                    ->label('Periode')
                    ->state(fn (EmployeeSapSnapshot $record): string => sprintf('%02d/%d', $record->period_month, $record->period_year))
                    ->sortable(['period_year', 'period_month']),
                TextColumn::make('source_file_name')
                    ->label('File')
                    ->searchable(),
                TextColumn::make('total_rows')
                    ->label('Total Baris')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('differences_count')
                    ->counts('differences')
                    ->label('Selisih')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'success'),
                TextColumn::make('importedBy.name')
                    ->label('Diimpor Oleh')
                    ->placeholder('System'),
                TextColumn::make('imported_at')
                    ->label('Tanggal Import')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('period_month')
                    ->label('Bulan')
                    ->options(self::monthOptions()),
                SelectFilter::make('period_year')
                    ->label('Tahun')
                    ->options(fn (): array => EmployeeSapSnapshot::query()
                        ->select('period_year')
                        ->distinct()
                        ->orderByDesc('period_year')
                        ->pluck('period_year', 'period_year')
                        ->all()),
            ])
            ->defaultSort('imported_at', 'desc')
            ->paginated([10, 25, 50])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            DifferencesRelationManager::class,
            RowsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeSapSnapshots::route('/'),
            'view' => ViewEmployeeSapSnapshot::route('/{record}'),
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function monthOptions(): array
    {
        return [
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
        ];
    }

    public static function importAction(): ImportAction
    {
        return ImportAction::make()
            ->label('Impor Snapshot SAP')
            ->importer(EmployeeSapSnapshotRowImporter::class)
            ->slideOver();
    }
}
