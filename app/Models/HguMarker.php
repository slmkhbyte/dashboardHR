<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HguMarker extends Model
{
    use HasFactory;

    public const MARKER_TYPE_BETON = 'beton';

    public const MARKER_TYPE_PARALON = 'paralon';

    public const CONDITION_BAIK = 'baik';

    public const CONDITION_RUSAK_RINGAN = 'rusak_ringan';

    public const CONDITION_RUSAK_BERAT = 'rusak_berat';

    public const CONDITION_HILANG = 'hilang';

    public const MARKER_TYPES = [
        self::MARKER_TYPE_BETON => 'Beton',
        self::MARKER_TYPE_PARALON => 'Paralon',
    ];

    public const CONDITIONS = [
        self::CONDITION_BAIK => 'Baik',
        self::CONDITION_RUSAK_RINGAN => 'Rusak Ringan',
        self::CONDITION_RUSAK_BERAT => 'Rusak Berat',
        self::CONDITION_HILANG => 'Hilang',
    ];

    protected $fillable = [
        'marker_number',
        'afdeling',
        'utm_coordinates',
        'latitude',
        'longitude',
        'marker_type',
        'condition',
        'is_moved',
        'last_checked_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'afdeling' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_moved' => 'boolean',
            'last_checked_at' => 'date',
        ];
    }

    public function getAfdelingLabelAttribute(): ?string
    {
        if (blank($this->afdeling)) {
            return null;
        }

        return match ((int) $this->afdeling) {
            1 => 'Afdeling I',
            2 => 'Afdeling II',
            3 => 'Afdeling III',
            4 => 'Afdeling IV',
            5 => 'Afdeling V',
            6 => 'Afdeling VI',
            7 => 'Afdeling VII',
            8 => 'Afdeling VIII',
            default => null,
        };
    }

    public function getGoogleMapsUrlAttribute(): ?string
    {
        if (is_null($this->latitude) || is_null($this->longitude)) {
            return null;
        }

        return 'https://www.google.com/maps/search/?api=1&query=' . $this->latitude . ',' . $this->longitude;
    }

    public function photos(): HasMany
    {
        return $this->hasMany(HguMarkerPhoto::class);
    }

    public function moves(): HasMany
    {
        return $this->hasMany(HguMarkerMove::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(HguMarkerHistory::class);
    }

    /**
     * @return array<string, string>
     */
    public static function getMarkerTypeOptions(): array
    {
        $dynamicOptions = static::query()
            ->whereNotNull('marker_type')
            ->distinct()
            ->pluck('marker_type')
            ->filter(fn (?string $value): bool => filled($value))
            ->mapWithKeys(fn (string $value): array => [trim($value) => trim($value)])
            ->all();

        return array_replace($dynamicOptions, self::MARKER_TYPES);
    }
}
