<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocumentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_document_id',
        'event',
        'old_values',
        'new_values',
        'old_image_blob',
        'old_image_mime_type',
        'old_image_original_filename',
        'old_image_size_bytes',
        'new_image_blob',
        'new_image_mime_type',
        'new_image_original_filename',
        'new_image_size_bytes',
        'changed_by',
    ];

    protected $hidden = [
        'old_image_blob',
        'new_image_blob',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function employeeDocument(): BelongsTo
    {
        return $this->belongsTo(EmployeeDocument::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getOldImageUrlAttribute(): ?string
    {
        if (blank($this->old_image_blob)) {
            return null;
        }

        return route('employee-document-histories.image', [
            'employeeDocumentHistory' => $this,
            'version' => 'old',
        ]);
    }

    public function getOldImageDownloadUrlAttribute(): ?string
    {
        if (blank($this->old_image_blob)) {
            return null;
        }

        return route('employee-document-histories.image', [
            'employeeDocumentHistory' => $this,
            'version' => 'old',
            'download' => 1,
        ]);
    }

    public function getNewImageUrlAttribute(): ?string
    {
        if (blank($this->new_image_blob)) {
            return null;
        }

        return route('employee-document-histories.image', [
            'employeeDocumentHistory' => $this,
            'version' => 'new',
        ]);
    }

    public function getNewImageDownloadUrlAttribute(): ?string
    {
        if (blank($this->new_image_blob)) {
            return null;
        }

        return route('employee-document-histories.image', [
            'employeeDocumentHistory' => $this,
            'version' => 'new',
            'download' => 1,
        ]);
    }
}
