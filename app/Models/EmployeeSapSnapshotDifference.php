<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeSapSnapshotDifference extends Model
{
    use HasFactory;

    protected $fillable = [
        'snapshot_id',
        'employee_id',
        'nik_sap',
        'name',
        'difference_count',
        'detected_at',
    ];

    protected function casts(): array
    {
        return [
            'difference_count' => 'integer',
            'detected_at' => 'datetime',
        ];
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(EmployeeSapSnapshot::class, 'snapshot_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(EmployeeSapSnapshotDifferenceItem::class, 'difference_id');
    }
}
