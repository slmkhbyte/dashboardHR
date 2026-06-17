<?php

namespace App\Filament\Resources\EmployeeSapSnapshots;

use App\Filament\Resources\EmployeeSapSnapshots\Pages\ListEmployeeSapSnapshotImportFailedRows;
use App\Filament\Resources\EmployeeSapSnapshots\Pages\ListEmployeeSapSnapshotImports;
use App\Filament\Resources\EmployeeSapSnapshots\Pages\ListEmployeeSapSnapshots;
use App\Filament\Resources\EmployeeSapSnapshots\Pages\ViewEmployeeSapSnapshot;
use App\Filament\Resources\EmployeeSapSnapshots\RelationManagers\DifferencesRelationManager;
use App\Filament\Resources\EmployeeSapSnapshots\RelationManagers\RowsRelationManager;
use App\Models\EmployeeSapSnapshot;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmployeeSapSnapshotResource extends Resource
{
    protected static ?string $model = EmployeeSapSnapshot::class;

    protected static ?string $recordTitleAttribute = 'period_label';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string | \UnitEnum | null $navigationGroup = 'Operasional HR';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Karyawan SAP';

    protected static ?string $modelLabel = 'Snapshot Karyawan SAP';

    protected static ?string $pluralModelLabel = 'Karyawan SAP';

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Snapshot')
                ->schema([
                    TextEntry::make('period_label')
                        ->label('Periode'),
                    TextEntry::make('source_file_name')
                        ->label('File')
                        ->placeholder('-'),
                    TextEntry::make('importedBy.name')
                        ->label('Diimpor Oleh')
                        ->placeholder('System'),
                    TextEntry::make('imported_at')
                        ->label('Diimpor')
                        ->dateTime('d M Y H:i')
                        ->placeholder('-'),
                    TextEntry::make('notes')
                        ->label('Catatan')
                        ->placeholder('-')
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->orderByDesc('period_year')->orderByDesc('period_month'))
            ->columns([
                TextColumn::make('period_label')
                    ->label('Periode'),
                TextColumn::make('rows_count')
                    ->counts('rows')
                    ->label('Data SAP')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('differences_count')
                    ->counts('differences')
                    ->label('Karyawan Berbeda')
                    ->numeric()
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'success')
                    ->sortable(),
                TextColumn::make('source_file_name')
                    ->label('File')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('importedBy.name')
                    ->label('Diimpor Oleh')
                    ->placeholder('System')
                    ->toggleable(),
                TextColumn::make('imported_at')
                    ->label('Diimpor')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->paginated([10, 25, 50])
            ->recordActions([
                ViewAction::make()
                    ->label('Buka'),
                DeleteAction::make(),
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
            'imports' => ListEmployeeSapSnapshotImports::route('/imports'),
            'failed-import-rows' => ListEmployeeSapSnapshotImportFailedRows::route('/imports/{import}/failed-rows'),
            'view' => ViewEmployeeSapSnapshot::route('/{record}'),
        ];
    }
}
