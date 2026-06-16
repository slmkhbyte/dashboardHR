<?php

namespace App\Models;

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
        'imported_at',
        'source_file_name',
        'total_rows',
        'import_id',
        'imported_by',
    ];

    protected function casts(): array
    {
        return [
            'period_month' => 'integer',
            'period_year' => 'integer',
            'imported_at' => 'datetime',
            'total_rows' => 'integer',
        ];
    }

    public function rows(): HasMany
    {
        return $this->hasMany(EmployeeSapSnapshotRow::class, 'snapshot_id');
    }

    public function differences(): HasMany
    {
        return $this->hasMany(EmployeeSapSnapshotDifference::class, 'snapshot_id');
    }

    public function importedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}
