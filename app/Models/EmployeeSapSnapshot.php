<?php

namespace App\Models;

use Filament\Actions\Imports\Models\Import;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeSapSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_month',
        'period_year',
        'source_file_name',
        'notes',
        'import_id',
        'imported_by',
        'imported_at',
    ];

    protected function casts(): array
    {
        return [
            'period_month' => 'integer',
            'period_year' => 'integer',
            'imported_at' => 'datetime',
        ];
    }

    public function getPeriodLabelAttribute(): string
    {
        return now()
            ->setDate($this->period_year, $this->period_month, 1)
            ->translatedFormat('F Y');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(EmployeeSapSnapshotRow::class);
    }

    public function differences(): HasMany
    {
        return $this->hasMany(EmployeeSapSnapshotDifference::class);
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }

    public function importedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}
