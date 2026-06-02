<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HguMarkerPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'hgu_marker_id',
        'photo_path',
        'caption',
        'uploaded_at',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
        ];
    }

    public function marker(): BelongsTo
    {
        return $this->belongsTo(HguMarker::class, 'hgu_marker_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
