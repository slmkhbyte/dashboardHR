<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HguMarker extends Model
{
    use HasFactory;

    public const MARKER_TYPES = [
        'beton' => 'Beton',
        'paralon' => 'Paralon',
    ];

    public const CONDITIONS = [
        'baik' => 'Baik',
        'rusak_ringan' => 'Rusak Ringan',
        'rusak_berat' => 'Rusak Berat',
        'hilang' => 'Hilang',
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
}
