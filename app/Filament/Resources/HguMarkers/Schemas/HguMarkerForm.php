<?php

namespace App\Filament\Resources\HguMarkers\Schemas;

use App\Models\HguMarker;
use App\Support\GeoCoordinateParser;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class HguMarkerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Patok')
                    ->schema([
                        TextInput::make('marker_number')
                            ->label('Nomor Patok')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Select::make('afdeling')
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
                            ])
                            ->searchable(),
                        Select::make('marker_type')
                            ->label('Jenis Patok')
                            ->required()
                            ->default('beton')
                            ->options(HguMarker::MARKER_TYPES),
                        Select::make('condition')
                            ->label('Keadaan / Kondisi Patok')
                            ->required()
                            ->default('baik')
                            ->options(HguMarker::CONDITIONS),
                        DatePicker::make('last_checked_at')
                            ->label('Pengecekan Terakhir'),
                    ])
                    ->columns(2),
                Section::make('Koordinat')
                    ->schema([
                        TextInput::make('utm_coordinates')
                            ->label('Titik Koordinat UTM')
                            ->placeholder('Contoh: 48M 123456 9876543')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('longitude')
                            ->hidden()
                            ->dehydrated()
                            ->afterStateHydrated(function (Set $set, ?float $state): void {
                                if (is_null($state)) {
                                    return;
                                }

                                $parts = GeoCoordinateParser::getLongitudeDmsParts($state);

                                if (! $parts) {
                                    return;
                                }

                                $set('longitude_degrees', $parts['degrees']);
                                $set('longitude_minutes', $parts['minutes']);
                                $set('longitude_seconds', $parts['seconds']);
                                $set('longitude_hemisphere', $parts['hemisphere']);
                            }),
                        TextInput::make('latitude')
                            ->hidden()
                            ->dehydrated()
                            ->afterStateHydrated(function (Set $set, ?float $state): void {
                                if (is_null($state)) {
                                    return;
                                }

                                $parts = GeoCoordinateParser::getLatitudeDmsParts($state);

                                if (! $parts) {
                                    return;
                                }

                                $set('latitude_degrees', $parts['degrees']);
                                $set('latitude_minutes', $parts['minutes']);
                                $set('latitude_seconds', $parts['seconds']);
                                $set('latitude_hemisphere', $parts['hemisphere']);
                            }),
                        Grid::make()
                            ->schema([
                                TextInput::make('longitude_degrees')
                                    ->label('Longitude Derajat')
                                    ->helperText('0–180')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(180)
                                    ->reactive()
                                    ->live(onBlur: true)
                                    ->rules(['nullable', 'integer', 'min:0', 'max:180', 'required_with:longitude_minutes,longitude_seconds,longitude_hemisphere'])
                                    ->afterStateUpdated(fn (Set $set, Get $get, ?string $state) => $set(
                                        'longitude',
                                        GeoCoordinateParser::composeDecimalFromDms(
                                            $state,
                                            $get('longitude_minutes'),
                                            $get('longitude_seconds'),
                                            $get('longitude_hemisphere'),
                                        ),
                                    )),
                                TextInput::make('longitude_minutes')
                                    ->label('Longitude Menit')
                                    ->helperText('0–59')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(59)
                                    ->reactive()
                                    ->live(onBlur: true)
                                    ->rules(['nullable', 'integer', 'min:0', 'max:59', 'required_with:longitude_degrees,longitude_seconds,longitude_hemisphere'])
                                    ->afterStateUpdated(fn (Set $set, Get $get, ?string $state) => $set(
                                        'longitude',
                                        GeoCoordinateParser::composeDecimalFromDms(
                                            $get('longitude_degrees'),
                                            $state,
                                            $get('longitude_seconds'),
                                            $get('longitude_hemisphere'),
                                        ),
                                    )),
                                TextInput::make('longitude_seconds')
                                    ->label('Longitude Detik')
                                    ->helperText('0–59,999')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(59.999)
                                    ->step(0.001)
                                    ->reactive()
                                    ->live(onBlur: true)
                                    ->rules(['nullable', 'numeric', 'min:0', 'max:59.999', 'required_with:longitude_degrees,longitude_minutes,longitude_hemisphere'])
                                    ->afterStateUpdated(fn (Set $set, Get $get, ?string $state) => $set(
                                        'longitude',
                                        GeoCoordinateParser::composeDecimalFromDms(
                                            $get('longitude_degrees'),
                                            $get('longitude_minutes'),
                                            $state,
                                            $get('longitude_hemisphere'),
                                        ),
                                    )),
                                Select::make('longitude_hemisphere')
                                    ->label('Hemisfer Longitude')
                                    ->options([
                                        'E' => 'E',
                                        'W' => 'W',
                                    ])
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(fn (Set $set, Get $get, ?string $state) => $set(
                                        'longitude',
                                        GeoCoordinateParser::composeDecimalFromDms(
                                            $get('longitude_degrees'),
                                            $get('longitude_minutes'),
                                            $get('longitude_seconds'),
                                            $state,
                                        ),
                                    ))
                                    ->rules(['nullable', 'in:E,W', 'required_with:longitude_degrees,longitude_minutes,longitude_seconds']),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                        Grid::make()
                            ->schema([
                                TextInput::make('latitude_degrees')
                                    ->label('Latitude Derajat')
                                    ->helperText('0–90')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(90)
                                    ->reactive()
                                    ->live(onBlur: true)
                                    ->rules(['nullable', 'integer', 'min:0', 'max:90', 'required_with:latitude_minutes,latitude_seconds,latitude_hemisphere'])
                                    ->afterStateUpdated(fn (Set $set, Get $get, ?string $state) => $set(
                                        'latitude',
                                        GeoCoordinateParser::composeDecimalFromDms(
                                            $state,
                                            $get('latitude_minutes'),
                                            $get('latitude_seconds'),
                                            $get('latitude_hemisphere'),
                                        ),
                                    )),
                                TextInput::make('latitude_minutes')
                                    ->label('Latitude Menit')
                                    ->helperText('0–59')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(59)
                                    ->reactive()
                                    ->live(onBlur: true)
                                    ->rules(['nullable', 'integer', 'min:0', 'max:59', 'required_with:latitude_degrees,latitude_seconds,latitude_hemisphere'])
                                    ->afterStateUpdated(fn (Set $set, Get $get, ?string $state) => $set(
                                        'latitude',
                                        GeoCoordinateParser::composeDecimalFromDms(
                                            $get('latitude_degrees'),
                                            $state,
                                            $get('latitude_seconds'),
                                            $get('latitude_hemisphere'),
                                        ),
                                    )),
                                TextInput::make('latitude_seconds')
                                    ->label('Latitude Detik')
                                    ->helperText('0–59,999')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(59.999)
                                    ->step(0.001)
                                    ->reactive()
                                    ->live(onBlur: true)
                                    ->rules(['nullable', 'numeric', 'min:0', 'max:59.999', 'required_with:latitude_degrees,latitude_minutes,latitude_hemisphere'])
                                    ->afterStateUpdated(fn (Set $set, Get $get, ?string $state) => $set(
                                        'latitude',
                                        GeoCoordinateParser::composeDecimalFromDms(
                                            $get('latitude_degrees'),
                                            $get('latitude_minutes'),
                                            $state,
                                            $get('latitude_hemisphere'),
                                        ),
                                    )),
                                Select::make('latitude_hemisphere')
                                    ->label('Hemisfer Latitude')
                                    ->options([
                                        'N' => 'N',
                                        'S' => 'S',
                                    ])
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(fn (Set $set, Get $get, ?string $state) => $set(
                                        'latitude',
                                        GeoCoordinateParser::composeDecimalFromDms(
                                            $get('latitude_degrees'),
                                            $get('latitude_minutes'),
                                            $get('latitude_seconds'),
                                            $state,
                                        ),
                                    ))
                                    ->rules(['nullable', 'in:N,S', 'required_with:latitude_degrees,latitude_minutes,latitude_seconds']),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
                Section::make('Catatan')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
