<?php

namespace App\Filament\Resources\HguMarkers\Tables;

use App\Models\HguMarker;
use App\Support\GeoCoordinateParser;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class HguMarkersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('marker_number')
                    ->label('Nomor Patok')
                    ->searchable()
                    ->sortable(query: fn ($query, string $direction) => $query->orderByRaw('CAST(marker_number AS INTEGER) ' . $direction)),
                TextColumn::make('afdeling_label')
                    ->label('Afdeling')
                    ->state(fn (HguMarker $record): ?string => $record->afdeling_label)
                    ->sortable(query: fn ($query, string $direction) => $query->orderBy('afdeling', $direction)),
                TextColumn::make('utm_coordinates')
                    ->label('Koordinat UTM')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('latitude')
                    ->label('Koordinat Geografis')
                    ->formatStateUsing(function (HguMarker $record): ?string {
                        if (is_null($record->latitude) || is_null($record->longitude)) {
                            return null;
                        }
                        return GeoCoordinateParser::formatLongitudeDms($record->longitude) . ', ' . GeoCoordinateParser::formatLatitudeDms($record->latitude);
                    })
                    ->toggleable(),
                TextColumn::make('marker_type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => HguMarker::MARKER_TYPES[$state] ?? $state),
                TextColumn::make('condition')
                    ->label('Kondisi')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => HguMarker::CONDITIONS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'baik' => 'success',
                        'rusak_ringan' => 'warning',
                        'rusak_berat' => 'danger',
                        'hilang' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('moves_count')
                    ->label('Pindah')
                    ->counts('moves')
                    ->formatStateUsing(fn (int $state): string => $state > 0 ? "{$state}x" : 'Tidak')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'gray'),
                TextColumn::make('last_checked_at')
                    ->label('Pengecekan Terakhir')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('photos_count')
                    ->label('Foto')
                    ->counts('photos')
                    ->badge()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('afdeling')
                    ->label('Afdeling')
                    ->options([
                        1 => 'Afdeling I',
                        2 => 'Afdeling II',
                        3 => 'Afdeling III',
                        4 => 'Afdeling IV',
                        5 => 'Afdeling V',
                        6 => 'Afdeling VI',
                        7 => 'Afdeling VII',
                        8 => 'Afdeling VIII',
                    ]),
                SelectFilter::make('condition')
                    ->label('Kondisi')
                    ->options(HguMarker::CONDITIONS),
                SelectFilter::make('marker_type')
                    ->label('Jenis Patok')
                    ->options(HguMarker::MARKER_TYPES),
                TernaryFilter::make('is_moved')
                    ->label('Patok Dipindah'),
            ])
            ->defaultSort('marker_number', 'asc')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->recordActions([
                Action::make('googleMaps')
                    ->label('Maps')
                    ->icon('heroicon-m-map-pin')
                    ->color('gray')
                    ->url(fn (HguMarker $record): ?string => $record->google_maps_url, shouldOpenInNewTab: true)
                    ->visible(fn (HguMarker $record): bool => filled($record->google_maps_url)),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
