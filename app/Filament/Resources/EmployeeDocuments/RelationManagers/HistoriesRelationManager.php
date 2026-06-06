<?php

namespace App\Filament\Resources\EmployeeDocuments\RelationManagers;

use App\Models\EmployeeDocumentHistory;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'histories';

    protected static ?string $title = 'Riwayat Perubahan';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event')
                    ->label('Event')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('changedBy.name')
                    ->label('Diubah Oleh')
                    ->placeholder('System'),
                TextColumn::make('old_values')
                    ->label('Data Lama')
                    ->formatStateUsing(fn (mixed $state): ?string => $this->formatValues($state))
                    ->wrap()
                    ->limit(120),
                TextColumn::make('new_values')
                    ->label('Data Baru')
                    ->formatStateUsing(fn (mixed $state): ?string => $this->formatValues($state))
                    ->wrap()
                    ->limit(120),
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('openOldImage')
                    ->label('Buka Gambar Lama')
                    ->icon('heroicon-o-photo')
                    ->url(fn (EmployeeDocumentHistory $record): ?string => $record->old_image_url)
                    ->openUrlInNewTab()
                    ->visible(fn (EmployeeDocumentHistory $record): bool => filled($record->old_image_url)),
                Action::make('openNewImage')
                    ->label('Buka Gambar Baru')
                    ->icon('heroicon-o-photo')
                    ->url(fn (EmployeeDocumentHistory $record): ?string => $record->new_image_url)
                    ->openUrlInNewTab()
                    ->visible(fn (EmployeeDocumentHistory $record): bool => filled($record->new_image_url)),
                Action::make('downloadOldImage')
                    ->label('Unduh Gambar Lama')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (EmployeeDocumentHistory $record): ?string => $record->old_image_download_url)
                    ->openUrlInNewTab()
                    ->visible(fn (EmployeeDocumentHistory $record): bool => filled($record->old_image_download_url)),
                Action::make('downloadNewImage')
                    ->label('Unduh Gambar Baru')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (EmployeeDocumentHistory $record): ?string => $record->new_image_download_url)
                    ->openUrlInNewTab()
                    ->visible(fn (EmployeeDocumentHistory $record): bool => filled($record->new_image_download_url)),
            ])
            ->paginated([10, 25, 50]);
    }

    private function formatValues(mixed $values): ?string
    {
        if (blank($values)) {
            return null;
        }

        if (is_string($values)) {
            $decoded = json_decode($values, true);

            if (is_array($decoded)) {
                $values = $decoded;
            }
        }

        if (! is_array($values)) {
            return (string) $values;
        }

        return collect($values)
            ->map(fn (mixed $value, string $key): string => "{$key}: " . json_encode($value))
            ->implode(', ');
    }
}
