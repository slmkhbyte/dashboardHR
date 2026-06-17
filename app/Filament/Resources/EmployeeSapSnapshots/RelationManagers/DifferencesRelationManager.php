<?php

namespace App\Filament\Resources\EmployeeSapSnapshots\RelationManagers;

use App\Models\EmployeeSapSnapshotDifference;
use App\Models\EmployeeSapSnapshotDifferenceItem;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class DifferencesRelationManager extends RelationManager
{
    protected static string $relationship = 'differences';

    protected static ?string $title = 'Perbedaan Data';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('items'))
            ->columns([
                TextColumn::make('nik_sap')
                    ->label('NIK SAP')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee_name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('difference_count')
                    ->label('Jumlah Perbedaan')
                    ->badge()
                    ->color('warning')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('change_summary')
                    ->label('Jenis Perubahan')
                    ->state(fn (EmployeeSapSnapshotDifference $record): string => $record->items->pluck('field_label')->implode(', '))
                    ->wrap()
                    ->limit(80),
                TextColumn::make('recorded_progress')
                    ->label('Tercatat di Pusat')
                    ->state(function (EmployeeSapSnapshotDifference $record): string {
                        $total = $record->items->count();
                        $recorded = $record->items->where('is_recorded_in_sap', true)->count();

                        return "{$recorded} / {$total}";
                    })
                    ->badge()
                    ->color(function (EmployeeSapSnapshotDifference $record): string {
                        $total = $record->items->count();
                        $recorded = $record->items->where('is_recorded_in_sap', true)->count();

                        return $total > 0 && $recorded === $total ? 'success' : 'warning';
                    }),
                TextColumn::make('detected_at')
                    ->label('Terdeteksi')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('detected_at', 'desc')
            ->paginated([10, 25, 50])
            ->recordActions([
                Action::make('details')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn (EmployeeSapSnapshotDifference $record): string => 'Detail Perbedaan ' . ($record->employee_name ?? $record->nik_sap))
                    ->schema([
                        Placeholder::make('items')
                            ->label('')
                            ->content(fn (EmployeeSapSnapshotDifference $record): HtmlString => new HtmlString($this->renderItemsTable($record))),
                    ])
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
                Action::make('recorded')
                    ->label('Catat di Pusat')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->schema([
                        CheckboxList::make('item_ids')
                            ->label('Field yang sudah tercatat di pusat')
                            ->options(fn (EmployeeSapSnapshotDifference $record): array => $record->items
                                ->where('is_recorded_in_sap', false)
                                ->mapWithKeys(fn (EmployeeSapSnapshotDifferenceItem $item): array => [
                                    $item->getKey() => "{$item->field_label}: {$item->sap_value} -> {$item->local_value}",
                                ])
                                ->all())
                            ->columns(1)
                            ->required(),
                        Textarea::make('remark')
                            ->label('Remark')
                            ->rows(3),
                    ])
                    ->action(function (EmployeeSapSnapshotDifference $record, array $data): void {
                        EmployeeSapSnapshotDifferenceItem::query()
                            ->whereIn('id', $data['item_ids'] ?? [])
                            ->where('employee_sap_snapshot_difference_id', $record->getKey())
                            ->update([
                                'is_recorded_in_sap' => true,
                                'recorded_in_sap_at' => now(),
                                'recorded_in_sap_by' => auth()->id(),
                                'remark' => $data['remark'] ?? null,
                            ]);

                        Notification::make()
                            ->title('Perubahan ditandai sudah tercatat di pusat')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (EmployeeSapSnapshotDifference $record): bool => $record->items->contains('is_recorded_in_sap', false)),
            ]);
    }

    private function renderItemsTable(EmployeeSapSnapshotDifference $record): string
    {
        $rows = $record->items
            ->map(function (EmployeeSapSnapshotDifferenceItem $item): string {
                $status = $item->is_recorded_in_sap ? 'Sudah tercatat' : 'Belum tercatat';
                $changedAt = $item->local_changed_at?->translatedFormat('d M Y H:i') ?? '-';

                return '<tr>'
                    . '<td class="px-3 py-2 font-medium">' . e($item->field_label) . '</td>'
                    . '<td class="px-3 py-2">' . e($item->sap_value ?? '-') . '</td>'
                    . '<td class="px-3 py-2">' . e($item->local_value ?? '-') . '</td>'
                    . '<td class="px-3 py-2">' . e($changedAt) . '</td>'
                    . '<td class="px-3 py-2">' . e($status) . '</td>'
                    . '<td class="px-3 py-2">' . e($item->remark ?? '-') . '</td>'
                    . '</tr>';
            })
            ->implode('');

        return <<<HTML
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-white/10">
        <thead>
            <tr class="text-left">
                <th class="px-3 py-2">Field</th>
                <th class="px-3 py-2">Nilai SAP</th>
                <th class="px-3 py-2">Nilai Lokal</th>
                <th class="px-3 py-2">Tgl Perubahan Lokal</th>
                <th class="px-3 py-2">Status</th>
                <th class="px-3 py-2">Remark</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-white/10">{$rows}</tbody>
    </table>
</div>
HTML;
    }
}
