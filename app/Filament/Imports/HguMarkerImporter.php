<?php

namespace App\Filament\Imports;

use App\Models\HguMarker;
use App\Support\GeoCoordinateParser;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Illuminate\Validation\ValidationException;
use Throwable;

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
                ->castStateUsing(fn (mixed $originalState, mixed $state): mixed => self::normalizeMarkerNumber($originalState ?? $state))
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('utm_coordinates')
                ->label('Koordinat UTM')
                ->guess(['utm', 'utm coordinates', 'utm coordinate', 'koordinat utm', 'utm combined', 'utm_combined', 'koordinat_utm'])
                ->exampleHeader('Koordinat UTM')
                ->example('49M 420553 9987669')
                ->helperText('Masukkan seluruh UTM dalam satu kolom, misalnya: 49M 420553 9987669')
                ->ignoreBlankState()
                ->rules(['nullable', 'string', 'max:255']),

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
                ->ignoreBlankState()
                ->rules(['nullable', 'numeric', 'between:-180,180']),

            ImportColumn::make('latitude')
                ->label('Koordinat Garis Lintang')
                ->guess(['koordinat garis lintang', 'lintang', 'latitude', 'lat'])
                ->exampleHeader('Koordinat Garis Lintang')
                ->example('0° 6\' 41,589" S')
                ->castStateUsing(fn (mixed $originalState, mixed $state): mixed => GeoCoordinateParser::parseLatitudeOrLongitude($originalState ?? $state))
                ->ignoreBlankState()
                ->rules(['nullable', 'numeric', 'between:-90,90']),

            ImportColumn::make('marker_type')
                ->label('Jenis Patok')
                ->exampleHeader('Jenis Patok')
                ->example('Pt.Semen')
                ->castStateUsing(fn (mixed $originalState, mixed $state): mixed => self::resolveMarkerType($originalState ?? $state))
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('condition')
                ->label('Keterangan')
                ->exampleHeader('Keterangan')
                ->example('Rusak')
                ->castStateUsing(fn (mixed $originalState, mixed $state): mixed => self::resolveCondition($originalState ?? $state))
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('afdeling')
                ->label('Afdeling')
                ->exampleHeader('Afdeling')
                ->example('I')
                ->castStateUsing(fn (mixed $originalState, mixed $state): mixed => self::parseAfdeling($originalState ?? $state))
                ->rules(['nullable', 'integer', 'between:1,8']),

            ImportColumn::make('notes')
                ->label('Catatan')
                ->exampleHeader('Catatan')
                ->example('Catatan tambahan')
                ->rules(['nullable', 'string']),
        ];
    }

    protected function beforeFill(): void
    {
        $this->data['marker_number'] = self::normalizeMarkerNumber($this->data['marker_number'] ?? null);

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

    protected function afterValidate(): void
    {
        $markerNumber = $this->data['marker_number'] ?? null;

        if (blank($markerNumber)) {
            return;
        }

        $cacheKey = $this->getSeenMarkerNumbersCacheKey();
        $seenMarkerNumbers = Cache::get($cacheKey, []);

        if (in_array($markerNumber, $seenMarkerNumbers, true)) {
            throw ValidationException::withMessages([
                'marker_number' => 'Duplicate marker number in import file.',
            ]);
        }

        $seenMarkerNumbers[] = $markerNumber;

        Cache::put($cacheKey, array_values(array_unique($seenMarkerNumbers)), now()->addDay());
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

    protected function beforeSave(): void
    {
        $this->record->marker_number = self::normalizeMarkerNumber($this->record->marker_number);
        $this->record->marker_type ??= $this->record->exists
            ? $this->record->getOriginal('marker_type')
            : HguMarker::MARKER_TYPE_BETON;
        $this->record->condition ??= $this->record->exists
            ? $this->record->getOriginal('condition')
            : HguMarker::CONDITION_BAIK;
    }

    public function saveRecord(): void
    {
        try {
            DB::transaction(function (): void {
                parent::saveRecord();
            });
        } catch (RowImportFailedException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            $this->logRowFailure($exception);

            throw new RowImportFailedException($this->formatRowImportFailureMessage($exception));
        }
    }

    public function getValidationMessages(): array
    {
        return [
            'marker_number.required' => 'Nomor Patok wajib diisi.',
        ];
    }

    public function getValidationAttributes(): array
    {
        return [
            'marker_number' => 'Nomor Patok',
            'marker_type' => 'Jenis Patok',
            'condition' => 'Keterangan',
            'afdeling' => 'Afdeling',
            'longitude' => 'Koordinat Garis Bujur',
            'latitude' => 'Koordinat Garis Lintang',
        ];
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

        $originalValue = trim($value);
        $value = strtolower($originalValue);

        if (str_contains($value, 'paralon')) {
            return HguMarker::MARKER_TYPE_PARALON;
        }

        if (str_contains($value, 'semen') || str_contains($value, 'beton')) {
            return HguMarker::MARKER_TYPE_BETON;
        }

        return $originalValue;
    }

    private static function resolveCondition(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $value = strtolower(trim($value));

        if (str_contains($value, 'baik')) {
            return HguMarker::CONDITION_BAIK;
        }

        if (str_contains($value, 'hilang')) {
            return HguMarker::CONDITION_HILANG;
        }

        if (str_contains($value, 'rusak berat')) {
            return HguMarker::CONDITION_RUSAK_BERAT;
        }

        if (str_contains($value, 'rusak ringan')) {
            return HguMarker::CONDITION_RUSAK_RINGAN;
        }

        if (str_contains($value, 'rusak')) {
            return HguMarker::CONDITION_RUSAK_RINGAN;
        }

        return null;
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

    private static function normalizeMarkerNumber(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $value = trim((string) $value);
        $value = preg_replace('/\s+/', ' ', $value);

        if (preg_match('/^\d+\.0+$/', $value)) {
            $value = strstr($value, '.', before_needle: true);
        }

        return $value;
    }

    private function getSeenMarkerNumbersCacheKey(): string
    {
        return 'imports:hgu-markers:seen-marker-numbers:' . $this->getImport()->getKey();
    }

    private function formatRowImportFailureMessage(Throwable $exception): string
    {
        $markerNumber = $this->data['marker_number'] ?? '(unknown)';

        return "Failed to import marker [{$markerNumber}]. {$exception->getMessage()}";
    }

    private function logRowFailure(Throwable $exception): void
    {
        Log::error('HGU marker import row failed.', [
            'import_id' => $this->getImport()->getKey(),
            'marker_number' => $this->data['marker_number'] ?? null,
            'row_data' => Arr::only($this->data, [
                'marker_number',
                'utm_coordinates',
                'longitude',
                'latitude',
                'marker_type',
                'condition',
                'afdeling',
                'notes',
            ]),
            'original_row_data' => Arr::only($this->getOriginalData(), [
                'marker_number',
                'utm_coordinates',
                'utm_x',
                'utm_y',
                'longitude',
                'latitude',
                'marker_type',
                'condition',
                'afdeling',
                'notes',
            ]),
            'exception_class' => $exception::class,
            'exception_message' => $exception->getMessage(),
        ]);
    }
}
