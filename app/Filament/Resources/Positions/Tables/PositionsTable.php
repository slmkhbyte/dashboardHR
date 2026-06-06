<?php

namespace App\Filament\Resources\Positions\Tables;

use App\Filament\Exports\PositionExporter;
use App\Models\Position;
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

class PositionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Jabatan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employees_count')
                    ->label('Karyawan Aktif')
                    ->counts([
                        'employees' => fn ($query) => $query->active(),
                    ])
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->since()
                    ->sortable(),
            ])
            ->filters([])
            ->defaultSort('name')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn (Position $record): bool => $record->isDefault()),
            ])
            ->toolbarActions([
                ExportAction::make()
                    ->label('Ekspor CSV')
                    ->exporter(PositionExporter::class),
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function (Collection $records): void {
                            $deletableRecords = $records->reject(
                                fn (Position $record): bool => $record->isDefault()
                            );

                            $deletedCount = 0;

                            foreach ($deletableRecords as $record) {
                                $record->delete();
                                $deletedCount++;
                            }

                            if ($records->count() !== $deletableRecords->count()) {
                                Notification::make()
                                    ->warning()
                                    ->title('Jabatan default tidak dihapus')
                                    ->body('Tanpa Jabatan dilewati karena merupakan jabatan default sistem.')
                                    ->send();
                            }

                            if ($deletedCount > 0) {
                                Notification::make()
                                    ->success()
                                    ->title('Jabatan berhasil dihapus')
                                    ->body("{$deletedCount} jabatan dipindahkan ke Tanpa Jabatan lalu dihapus.")
                                    ->send();
                            }
                        }),
                ]),
            ]);
    }
}
