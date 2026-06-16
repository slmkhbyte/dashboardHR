<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSapSnapshotDifferenceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'difference_id',
        'field_name',
        'field_label',
        'sap_value',
        'local_value',
        'local_changed_at',
        'is_recorded_in_sap',
        'recorded_in_sap_at',
        'remark',
    ];

    protected function casts(): array
    {
        return [
            'local_changed_at' => 'datetime',
            'is_recorded_in_sap' => 'boolean',
            'recorded_in_sap_at' => 'datetime',
        ];
    }

    public function difference(): BelongsTo
    {
        return $this->belongsTo(EmployeeSapSnapshotDifference::class, 'difference_id');
    }
}
