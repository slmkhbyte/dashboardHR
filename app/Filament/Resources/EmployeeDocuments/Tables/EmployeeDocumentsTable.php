<?php

namespace App\Filament\Resources\EmployeeDocuments\Tables;

use App\Filament\Exports\EmployeeDocumentExporter;
use App\Models\EmployeeDocument;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmployeeDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
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
