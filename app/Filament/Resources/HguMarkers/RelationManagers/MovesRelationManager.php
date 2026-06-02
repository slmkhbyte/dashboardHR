<?php

namespace App\Filament\Resources\HguMarkers\RelationManagers;

use App\Models\HguMarkerMove;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MovesRelationManager extends RelationManager
{
    protected static string $relationship = 'moves';

    protected static ?string $title = 'Riwayat Pindah Patok';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Lokasi Sebelum')
                    ->schema([
                        TextInput::make('from_utm_coordinates')
                            ->label('UTM Sebelum')
                            ->default(fn (): ?string => $this->getOwnerRecord()->utm_coordinates)
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('from_latitude')
                            ->label('Latitude Sebelum')
                            ->default(fn (): ?string => $this->getOwnerRecord()->latitude)
                            ->numeric()
                            ->minValue(-90)
                            ->maxValue(90),
                        TextInput::make('from_longitude')
                            ->label('Longitude Sebelum')
                            ->default(fn (): ?string => $this->getOwnerRecord()->longitude)
                            ->numeric()
                            ->minValue(-180)
                            ->maxValue(180),
                    ])
                    ->columns(2),
                Section::make('Lokasi Sesudah')
                    ->schema([
                        TextInput::make('to_utm_coordinates')
                            ->label('UTM Sesudah')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('to_latitude')
                            ->label('Latitude Sesudah')
                            ->numeric()
                            ->minValue(-90)
                            ->maxValue(90),
                        TextInput::make('to_longitude')
                            ->label('Longitude Sesudah')
                            ->numeric()
                            ->minValue(-180)
                            ->maxValue(180),
                    ])
                    ->columns(2),
                Section::make('Pemindah')
                    ->schema([
                        Select::make('moved_by_type')
                            ->label('Dipindahkan Oleh')
                            ->required()
                            ->default('internal')
                            ->options(HguMarkerMove::MOVED_BY_TYPES),
                        TextInput::make('moved_by_name')
                            ->label('Nama / Identitas Pemindah')
                            ->maxLength(255),
                    ])
                    ->columns(2),
                DatePicker::make('moved_at')
                    ->label('Tanggal Pindah'),
                Textarea::make('reason')
                    ->label('Alasan / Catatan')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('moved_at')
                    ->label('Tanggal Pindah')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('from_utm_coordinates')
                    ->label('UTM Sebelum')
                    ->toggleable(),
                TextColumn::make('to_utm_coordinates')
                    ->label('UTM Sesudah')
                    ->toggleable(),
                TextColumn::make('moved_by_type')
                    ->label('Dipindahkan Oleh')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => HguMarkerMove::MOVED_BY_TYPES[$state] ?? $state),
                TextColumn::make('moved_by_name')
                    ->label('Nama Pemindah')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('reason')
                    ->label('Catatan')
                    ->wrap()
                    ->limit(100),
                TextColumn::make('recordedBy.name')
                    ->label('Dicatat Oleh')
                    ->placeholder('System'),
            ])
            ->defaultSort('moved_at', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        $owner = $this->getOwnerRecord();

                        $data['from_utm_coordinates'] ??= $owner->utm_coordinates;
                        $data['from_latitude'] ??= $owner->latitude;
                        $data['from_longitude'] ??= $owner->longitude;
                        $data['recorded_by'] = auth()->id();

                        return $data;
                    })
                    ->after(function (HguMarkerMove $record): void {
                        $this->getOwnerRecord()->update([
                            'utm_coordinates' => $record->to_utm_coordinates,
                            'latitude' => $record->to_latitude,
                            'longitude' => $record->to_longitude,
                            'is_moved' => true,
                        ]);
                    }),
            ])
            ->recordActions([
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
