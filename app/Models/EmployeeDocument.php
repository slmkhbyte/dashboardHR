<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'document_name',
        'document_type',
        'document_number',
        'issued_at',
        'expires_at',
        'status',
        'notes',
        'image_blob',
        'image_mime_type',
        'image_original_filename',
        'image_size_bytes',
    ];

    protected $hidden = [
        'image_blob',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'expires_at' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(EmployeeDocumentHistory::class)->latest();
    }

    public function getImageUrlAttribute(): ?string
    {
        if (blank($this->image_blob)) {
            return null;
        }

        return route('employee-documents.image', [
            'employeeDocument' => $this,
            'v' => $this->getImageCacheVersion(),
        ]);
    }

    public function getImageDownloadUrlAttribute(): ?string
    {
        if (blank($this->image_blob)) {
            return null;
        }

        return route('employee-documents.image', [
            'employeeDocument' => $this,
            'download' => 1,
            'v' => $this->getImageCacheVersion(),
        ]);
    }

    private function getImageCacheVersion(): string
    {
        $updatedAt = $this->updated_at?->timestamp ?? 0;
        $filename = $this->image_original_filename ?? '';
        $size = $this->image_size_bytes ?? 0;

        return sha1($updatedAt . '|' . $filename . '|' . $size);
    }
}
