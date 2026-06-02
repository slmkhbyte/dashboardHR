<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HguMarkerHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'hgu_marker_id',
        'event',
        'old_values',
        'new_values',
        'changed_by',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function marker(): BelongsTo
    {
        return $this->belongsTo(HguMarker::class, 'hgu_marker_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
