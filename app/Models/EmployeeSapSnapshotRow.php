<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSapSnapshotRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'snapshot_id',
        'nik_sap',
        'name',
        'position',
        'work_unit',
        'lvl_bod',
        'employee_grade',
        'employment_status',
        'company',
        'department',
        'division',
        'unit',
        'location',
        'superior',
        'raw_data',
    ];

    protected function casts(): array
    {
        return [
            'lvl_bod' => 'integer',
            'raw_data' => 'array',
        ];
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(EmployeeSapSnapshot::class, 'snapshot_id');
    }
}
