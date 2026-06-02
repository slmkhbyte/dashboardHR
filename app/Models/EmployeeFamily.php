<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeFamily extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'name',
        'relationship',
        'gender',
        'birth_place',
        'birth_date',
        'last_education',
        'religion',
        'ethnicity',
        'address',
        'phone',
        'is_dependent',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'is_dependent' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
