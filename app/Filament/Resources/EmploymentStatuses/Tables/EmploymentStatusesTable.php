<?php

namespace App\Filament\Resources\EmploymentStatuses\Tables;

use App\Filament\Exports\EmploymentStatusExporter;
use App\Models\EmploymentStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmploymentStatusesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Status')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state, $record): string => $record->color ?? 'gray'),
                TextColumn::make('color')
                    ->label('Warna')
                    ->badge()
                    ->color(fn (string $state): string => $state),
                TextColumn::make('employees_count')
                    ->label('Karyawan Aktif')
                    ->counts([
                        'employees' => fn ($query) => $query->active(),
                    ])
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([])
            ->defaultSort('name')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn (EmploymentStatus $record): bool => $record->isDefault()),
            ])
            ->toolbarActions([
                ExportAction::make()
                    ->label('Ekspor CSV')
                    ->exporter(EmploymentStatusExporter::class),
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function (Collection $records): void {
                            $deletableRecords = $records->reject(
                                fn (EmploymentStatus $record): bool => $record->isDefault()
                            );

                            $deletedCount = 0;

                            foreach ($deletableRecords as $record) {
                                $record->delete();
                                $deletedCount++;
                            }

                            if ($records->count() !== $deletableRecords->count()) {
                                Notification::make()
                                    ->warning()
                                    ->title('Status default tidak dihapus')
                                    ->body('Tanpa Status Kerja dilewati karena merupakan status default sistem.')
                                    ->send();
                            }

                            if ($deletedCount > 0) {
                                Notification::make()
                                    ->success()
                                    ->title('Status kerja berhasil dihapus')
                                    ->body("{$deletedCount} status kerja dipindahkan ke Tanpa Status Kerja lalu dihapus.")
                                    ->send();
                            }
                        }),
                ]),
            ]);
    }
}
