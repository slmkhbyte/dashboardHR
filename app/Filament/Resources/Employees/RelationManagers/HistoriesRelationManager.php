<?php

namespace App\Filament\Resources\Employees\RelationManagers;

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
