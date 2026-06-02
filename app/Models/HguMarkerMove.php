<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HguMarkerMove extends Model
{
    use HasFactory;

    public const MOVED_BY_TYPES = [
        'internal' => 'Internal',
        'warga' => 'Warga',
        'pihak_lain' => 'Pihak Lain',
        'tidak_diketahui' => 'Tidak Diketahui',
    ];

    protected $fillable = [
        'hgu_marker_id',
        'from_utm_coordinates',
        'from_latitude',
        'from_longitude',
        'to_utm_coordinates',
        'to_latitude',
        'to_longitude',
        'moved_by_type',
        'moved_by_name',
        'moved_at',
        'reason',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'from_latitude' => 'decimal:7',
            'from_longitude' => 'decimal:7',
            'to_latitude' => 'decimal:7',
            'to_longitude' => 'decimal:7',
            'moved_at' => 'date',
        ];
    }

    public function marker(): BelongsTo
    {
        return $this->belongsTo(HguMarker::class, 'hgu_marker_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
