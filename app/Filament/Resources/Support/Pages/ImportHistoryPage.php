<?php

namespace App\Filament\Resources\Support\Pages;

use Filament\Actions\Action;
use Filament\Actions\Imports\Models\Import;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\CanAuthorizeResourceAccess;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

abstract class ImportHistoryPage extends Page implements HasTable
{
    use CanAuthorizeResourceAccess;
    use Tables\Concerns\InteractsWithTable;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $breadcrumb = 'Riwayat Import';

    abstract protected static function getImporterClass(): string;

    abstract protected static function getImportFailuresPageName(): string;

    public function getTitle(): string
    {
        return 'Riwayat Import';
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                EmbeddedTable::make(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('file_name')
                    ->label('File')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->state(fn (Import $record): string => $this->getImportStatus($record))
                    ->badge()
                    ->color(fn (Import $record): string => $this->getImportStatusColor($record)),
                TextColumn::make('successful_rows')
                    ->label('Berhasil')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('failed_rows')
                    ->label('Gagal')
                    ->state(fn (Import $record): int => $record->getFailedRowsCount())
                    ->badge()
                    ->color(fn (Import $record): string => $record->getFailedRowsCount() > 0 ? 'danger' : 'gray'),
                TextColumn::make('progress')
                    ->label('Progres')
                    ->state(fn (Import $record): string => "{$record->processed_rows} / {$record->total_rows}")
                    ->badge()
                    ->color('gray'),
                TextColumn::make('user.name')
                    ->label('Diimpor Oleh')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Mulai')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->label('Selesai')
                    ->placeholder('-')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'processing' => $query->whereNull('completed_at'),
                            'completed' => $query
                                ->whereNotNull('completed_at')
                                ->where('successful_rows', '>', 0),
                            'failed' => $query
                                ->whereNotNull('completed_at')
                                ->where('successful_rows', '=', 0),
                            default => $query,
                        };
                    }),
                Filter::make('created_at')
                    ->label('Tanggal Import')
                    ->schema([
                        DatePicker::make('from')
                            ->label('Dari'),
                        DatePicker::make('until')
                            ->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                filled($data['from'] ?? null),
                                fn (Builder $query): Builder => $query->whereDate('created_at', '>=', $data['from']),
                            )
                            ->when(
                                filled($data['until'] ?? null),
                                fn (Builder $query): Builder => $query->whereDate('created_at', '<=', $data['until']),
                            );
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->recordActions([
                Action::make('detailFailedRows')
                    ->label('Detail Gagal')
                    ->icon('heroicon-o-exclamation-circle')
                    ->color('danger')
                    ->url(fn (Import $record): string => static::getResource()::getUrl(static::getImportFailuresPageName(), ['import' => $record]))
                    ->visible(fn (Import $record): bool => $record->getFailedRowsCount() > 0),
                Action::make('downloadFailedRows')
                    ->label('Unduh Gagal')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('danger')
                    ->url(fn (Import $record): string => $this->getFailedRowsDownloadUrl($record), shouldOpenInNewTab: true)
                    ->visible(fn (Import $record): bool => $record->getFailedRowsCount() > 0),
            ])
            ->emptyStateHeading('Belum ada riwayat import')
            ->emptyStateDescription('Riwayat import akan tampil di sini setelah CSV diproses.');
    }

    protected function getTableQuery(): Builder
    {
        return Import::query()
            ->where('importer', static::getImporterClass())
            ->with('user');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali ke Data')
                ->icon('heroicon-o-arrow-left')
                ->url(static::getResource()::getUrl()),
        ];
    }

    protected function getImportStatus(Import $record): string
    {
        if (blank($record->completed_at)) {
            return 'processing';
        }

        if ($record->successful_rows === 0) {
            return 'failed';
        }

        return 'completed';
    }

    protected function getImportStatusColor(Import $record): string
    {
        return match ($this->getImportStatus($record)) {
            'processing' => 'info',
            'failed' => 'danger',
            default => $record->getFailedRowsCount() > 0 ? 'warning' : 'success',
        };
    }

    protected function getFailedRowsDownloadUrl(Import $import): string
    {
        return URL::signedRoute('filament.imports.failed-rows.download', [
            'authGuard' => Filament::getAuthGuard(),
            'import' => $import,
        ], absolute: false);
    }

    protected function resolveTableRecord(?string $key): ?Model
    {
        return $this->getTableQuery()->find($key);
    }
}
