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
        'photo_blob',
        'photo_mime_type',
        'original_filename',
        'photo_size_bytes',
        'caption',
        'uploaded_at',
        'uploaded_by',
    ];

    protected $hidden = [
        'photo_blob',
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

    public function getImageUrlAttribute(): ?string
    {
        if (blank($this->photo_blob)) {
            return null;
        }

        return route('hgu-marker-photos.show', $this);
    }
}
