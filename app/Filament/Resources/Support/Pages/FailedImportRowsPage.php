<?php

namespace App\Filament\Resources\Support\Pages;

use Filament\Actions\Action;
use Filament\Actions\Imports\Models\FailedImportRow;
use Filament\Actions\Imports\Models\Import;
use Filament\Facades\Filament;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\CanAuthorizeResourceAccess;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

abstract class FailedImportRowsPage extends Page implements HasTable
{
    use CanAuthorizeResourceAccess;
    use Tables\Concerns\InteractsWithTable;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $breadcrumb = 'Detail Gagal';

    public Import $import;

    abstract protected static function getImporterClass(): string;

    abstract protected static function getImportHistoryPageName(): string;

    public function mount(Import $import): void
    {
        abort_unless($import->importer === static::getImporterClass(), 404);

        $this->import = $import;
    }

    public function getTitle(): string
    {
        return 'Baris Gagal Import';
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
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('validation_error')
                    ->label('Error')
                    ->searchable()
                    ->wrap()
                    ->placeholder('System error: tidak ada detail kegagalan tersedia'),
                TextColumn::make('data')
                    ->label('Payload')
                    ->state(fn (FailedImportRow $record): array => collect($record->data)
                        ->map(fn (mixed $value, string $key): string => "{$key}: " . (filled($value) ? (is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE)) : '-'))
                        ->values()
                        ->all())
                    ->listWithLineBreaks()
                    ->limitList(4)
                    ->expandableLimitedList()
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('Tercatat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('id')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->emptyStateHeading('Tidak ada baris gagal')
            ->emptyStateDescription('Semua baris dalam import ini berhasil diproses.');
    }

    protected function getTableQuery(): Builder
    {
        return $this->import->failedRows()->getQuery();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadFailedRows')
                ->label('Unduh CSV Gagal')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('danger')
                ->url($this->getFailedRowsDownloadUrl(), shouldOpenInNewTab: true)
                ->visible($this->import->getFailedRowsCount() > 0),
            Action::make('back')
                ->label('Kembali ke Riwayat Import')
                ->icon('heroicon-o-arrow-left')
                ->url(static::getResource()::getUrl(static::getImportHistoryPageName())),
        ];
    }

    protected function getFailedRowsDownloadUrl(): string
    {
        return URL::signedRoute('filament.imports.failed-rows.download', [
            'authGuard' => Filament::getAuthGuard(),
            'import' => $this->import,
        ], absolute: false);
    }

    protected function resolveTableRecord(?string $key): ?Model
    {
        return $this->getTableQuery()->find($key);
    }
}
