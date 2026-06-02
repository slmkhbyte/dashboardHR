<?php

namespace App\Filament\Imports;

use App\Models\HguMarker;
use App\Support\GeoCoordinateParser;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class HguMarkerImporter extends Importer
{
    protected static ?string $model = HguMarker::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('marker_number')
                ->label('Nomor Patok')
                ->guess(['no patok', 'nomor patok', 'no. patok', 'patok', 'marker number'])
                ->requiredMapping()
                ->exampleHeader('No Patok')
                ->example('1')
                ->rules(['required']),

            ImportColumn::make('utm_coordinates')
                ->label('Koordinat UTM')
                ->guess(['utm', 'utm coordinates', 'utm coordinate', 'koordinat utm', 'utm combined', 'utm_combined', 'koordinat_utm'])
                ->exampleHeader('Koordinat UTM')
                ->example('49M 420553 9987669')
                ->helperText('Masukkan seluruh UTM dalam satu kolom, misalnya: 49M 420553 9987669')
                ->ignoreBlankState(),

            ImportColumn::make('utm_x')
                ->label('UTM 49 M sumbu X')
                ->guess(['utm x', 'utm 49 m sumbu x', 'sumbu x', 'x', 'utm_x', 'utm 49m x', 'easting', 'koordinat x'])
                ->exampleHeader('Sumbu X')
                ->example('420553')
                ->fillRecordUsing(fn (): null => null)
                ->ignoreBlankState(),

            ImportColumn::make('utm_y')
                ->label('UTM 49 M sumbu Y')
                ->guess(['utm y', 'utm 49 m sumbu y', 'sumbu y', 'y', 'utm_y', 'utm 49m y', 'northing', 'koordinat y'])
                ->exampleHeader('Sumbu Y')
                ->example('9987669')
                ->fillRecordUsing(fn (): null => null)
                ->ignoreBlankState(),

            ImportColumn::make('longitude')
                ->label('Koordinat Garis Bujur')
                ->guess(['koordinat garis bujur', 'bujur', 'longitude', 'long'])
                ->exampleHeader('Koordinat Garis Bujur')
                ->example('110° 17\' 9,763" E')
                ->castStateUsing(fn (mixed $originalState, mixed $state): mixed => GeoCoordinateParser::parseLatitudeOrLongitude($originalState ?? $state))
                ->ignoreBlankState(),

            ImportColumn::make('latitude')
                ->label('Koordinat Garis Lintang')
                ->guess(['koordinat garis lintang', 'lintang', 'latitude', 'lat'])
                ->exampleHeader('Koordinat Garis Lintang')
                ->example('0° 6\' 41,589" S')
                ->castStateUsing(fn (mixed $originalState, mixed $state): mixed => GeoCoordinateParser::parseLatitudeOrLongitude($originalState ?? $state))
                ->ignoreBlankState(),

            ImportColumn::make('marker_type')
                ->label('Jenis Patok')
                ->exampleHeader('Jenis Patok')
                ->example('Pt.Semen')
                ->castStateUsing(fn (mixed $originalState, mixed $state): mixed => self::resolveMarkerType($originalState ?? $state)),

            ImportColumn::make('condition')
                ->label('Keterangan')
                ->exampleHeader('Keterangan')
                ->example('Rusak')
                ->castStateUsing(fn (mixed $originalState, mixed $state): mixed => self::resolveCondition($originalState ?? $state)),

            ImportColumn::make('afdeling')
                ->label('Afdeling')
                ->exampleHeader('Afdeling')
                ->example('I')
                ->castStateUsing(fn (mixed $originalState, mixed $state): mixed => self::parseAfdeling($originalState ?? $state)),

            ImportColumn::make('notes')
                ->label('Catatan')
                ->exampleHeader('Catatan')
                ->example('Catatan tambahan'),
        ];
    }

    protected function beforeFill(): void
    {
        $utmCoordinates = GeoCoordinateParser::normalizeUtmCoordinates(
            $this->data['utm_coordinates'] ?? null,
            $this->data['utm_x'] ?? null,
            $this->data['utm_y'] ?? null,
        );

        if ($utmCoordinates !== null) {
            $this->data['utm_coordinates'] = $utmCoordinates;
        }

        if (isset($this->data['utm_x'])) {
            $this->data['utm_x'] = null;
        }
        if (isset($this->data['utm_y'])) {
            $this->data['utm_y'] = null;
        }
    }

    public function resolveRecord(): HguMarker
    {
        $record = HguMarker::query()->firstOrNew([
            'marker_number' => $this->data['marker_number'] ?? null,
        ]);

        return $record;
    }

    protected function afterFill(): void
    {
        $utmCoordinates = GeoCoordinateParser::normalizeUtmCoordinates(
            $this->data['utm_coordinates'] ?? null,
            $this->data['utm_x'] ?? null,
            $this->data['utm_y'] ?? null,
        );

        if ($utmCoordinates !== null) {
            $this->record->utm_coordinates = $utmCoordinates;
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your HGU marker import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    private static function resolveMarkerType(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $value = strtolower(trim($value));

        if (str_contains($value, 'paralon')) {
            return 'paralon';
        }

        if (str_contains($value, 'semen') || str_contains($value, 'beton')) {
            return 'beton';
        }

        return $value;
    }

    private static function resolveCondition(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $value = strtolower(trim($value));

        if (str_contains($value, 'baik')) {
            return 'baik';
        }

        if (str_contains($value, 'hilang')) {
            return 'hilang';
        }

        if (str_contains($value, 'rusak berat')) {
            return 'rusak_berat';
        }

        if (str_contains($value, 'rusak ringan')) {
            return 'rusak_ringan';
        }

        if (str_contains($value, 'rusak')) {
            return 'rusak_ringan';
        }

        return $value;
    }

    private static function parseAfdeling(?string $value): ?int
    {
        if (blank($value)) {
            return null;
        }

        $value = strtolower(trim($value));
        $value = str_replace(['afdeling', 'afd', '.', ' '], '', $value);

        $roman = [
            'i' => 1,
            'ii' => 2,
            'iii' => 3,
            'iv' => 4,
            'v' => 5,
            'vi' => 6,
            'vii' => 7,
            'viii' => 8,
        ];

        if (isset($roman[$value])) {
            return $roman[$value];
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }
}
