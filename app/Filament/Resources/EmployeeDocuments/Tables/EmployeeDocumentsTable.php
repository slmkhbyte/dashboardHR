<?php

namespace App\Filament\Resources\EmployeeDocuments\Tables;

use App\Filament\Exports\EmployeeDocumentExporter;
use App\Models\EmployeeDocument;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmployeeDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')
                    ->label('Preview')
                    ->getStateUsing(fn (EmployeeDocument $record): ?string => $record->image_url)
                    ->height(56)
                    ->square(),
                TextColumn::make('employee.full_name')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('document_name')
                    ->label('Dokumen')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('document_type')
                    ->label('Jenis')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('image_original_filename')
                    ->label('Lampiran')
                    ->placeholder('Belum ada gambar')
                    ->url(fn (EmployeeDocument $record): ?string => $record->image_url)
                    ->openUrlInNewTab(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'complete' => 'success',
                        'expiring' => 'warning',
                        'expired' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('expires_at')
                    ->label('Berlaku Sampai')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('document_type')
                    ->label('Jenis Dokumen')
                    ->options(fn (): array => EmployeeDocument::query()->distinct()->pluck('document_type', 'document_type')->all()),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'complete' => 'Complete',
                        'expiring' => 'Expiring',
                        'expired' => 'Expired',
                    ]),
            ])
            ->defaultSort('document_name')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->recordActions([
                Action::make('openImage')
                    ->label('Buka Gambar')
                    ->icon('heroicon-o-eye')
                    ->url(fn (EmployeeDocument $record): ?string => $record->image_url)
                    ->openUrlInNewTab()
                    ->visible(fn (EmployeeDocument $record): bool => filled($record->image_url)),
                Action::make('downloadImage')
                    ->label('Unduh Gambar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (EmployeeDocument $record): ?string => $record->image_download_url)
                    ->openUrlInNewTab()
                    ->visible(fn (EmployeeDocument $record): bool => filled($record->image_download_url)),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                ExportAction::make()
                    ->label('Ekspor CSV')
                    ->exporter(EmployeeDocumentExporter::class),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
