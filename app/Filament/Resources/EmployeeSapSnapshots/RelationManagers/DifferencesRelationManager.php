<?php

namespace App\Filament\Resources\EmployeeSapSnapshots\RelationManagers;

use App\Models\EmployeeSapSnapshotDifference;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DifferencesRelationManager extends RelationManager
{
    protected static string $relationship = 'differences';

    protected static ?string $title = 'Perbedaan SAP vs Lokal';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['items', 'employee']))
            ->columns([
                TextColumn::make('nik_sap')
                    ->label('NIK SAP')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('difference_count')
                    ->label('Jumlah')
                    ->badge()
                    ->color('warning')
                    ->sortable(),
                TextColumn::make('changed_fields')
                    ->label('Ringkasan Field')
                    ->state(fn (EmployeeSapSnapshotDifference $record): string => $record->items->pluck('field_label')->implode(', '))
                    ->wrap()
                    ->limit(80),
                TextColumn::make('detected_at')
                    ->label('Tanggal Deteksi')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('progress')
                    ->label('Progress')
                    ->state(fn (EmployeeSapSnapshotDifference $record): string => $record->items->where('is_recorded_in_sap', true)->count() . '/' . $record->items->count() . ' recorded')
                    ->badge()
                    ->color(fn (EmployeeSapSnapshotDifference $record): string => $record->items->every('is_recorded_in_sap') ? 'success' : 'warning'),
            ])
            ->recordActions([
                Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->slideOver()
                    ->modalSubmitActionLabel('Simpan Status Field')
                    ->schema([
                        Repeater::make('items')
                            ->label('Detail Perbedaan')
                            ->schema([
                                Hidden::make('id'),
                                TextInput::make('field_label')
                                    ->label('Field')
                                    ->disabled(),
                                Textarea::make('sap_value')
                                    ->label('Nilai SAP')
                                    ->rows(2)
                                    ->disabled(),
                                Textarea::make('local_value')
                                    ->label('Nilai Lokal')
                                    ->rows(2)
                                    ->disabled(),
                                DateTimePicker::make('local_changed_at')
                                    ->label('Tanggal Perubahan Lokal')
                                    ->disabled(),
                                Toggle::make('is_recorded_in_sap')
                                    ->label('Sudah Tercatat di SAP')
                                    ->live(),
                                DateTimePicker::make('recorded_in_sap_at')
                                    ->label('Tanggal Tercatat di SAP'),
                                Textarea::make('remark')
                                    ->label('Remark')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ])
                    ->fillForm(fn (EmployeeSapSnapshotDifference $record): array => [
                        'items' => $record->items()
                            ->orderBy('id')
                            ->get()
                            ->map(fn ($item): array => [
                                'id' => $item->getKey(),
                                'field_label' => $item->field_label,
                                'sap_value' => $item->sap_value,
                                'local_value' => $item->local_value,
                                'local_changed_at' => $item->local_changed_at,
                                'is_recorded_in_sap' => $item->is_recorded_in_sap,
                                'recorded_in_sap_at' => $item->recorded_in_sap_at,
                                'remark' => $item->remark,
                            ])
                            ->all(),
                    ])
                    ->action(function (EmployeeSapSnapshotDifference $record, array $data): void {
                        foreach ($data['items'] ?? [] as $itemData) {
                            $item = $record->items()->find($itemData['id'] ?? null);

                            if (! $item) {
                                continue;
                            }

                            $isRecorded = (bool) ($itemData['is_recorded_in_sap'] ?? false);

                            $item->update([
                                'is_recorded_in_sap' => $isRecorded,
                                'recorded_in_sap_at' => $isRecorded ? ($itemData['recorded_in_sap_at'] ?? now()) : null,
                                'remark' => $itemData['remark'] ?? null,
                            ]);
                        }
                    }),
                Action::make('markAllRecorded')
                    ->label('Mark All')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (EmployeeSapSnapshotDifference $record): int => $record->items()->update([
                        'is_recorded_in_sap' => true,
                        'recorded_in_sap_at' => now(),
                    ])),
            ])
            ->defaultSort('detected_at', 'desc')
            ->paginated([10, 25, 50])
            ->emptyStateHeading('Tidak ada perbedaan')
            ->emptyStateDescription('Snapshot ini sama dengan data karyawan lokal untuk field yang dipantau.');
    }
}
