<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSapSnapshotRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_sap_snapshot_id',
        'employee_id',
        'nik_sap',
        'nik_karyawan',
        'full_name',
        'position',
        'employment_status',
        'employee_grade',
        'work_unit',
        'lvl_bod',
        'hire_date',
        'is_active',
        'raw_data',
    ];

    protected function casts(): array
    {
        return [
            'lvl_bod' => 'integer',
            'hire_date' => 'date',
            'is_active' => 'boolean',
            'raw_data' => 'array',
        ];
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(EmployeeSapSnapshot::class, 'employee_sap_snapshot_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
