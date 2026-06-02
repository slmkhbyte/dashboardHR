<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'nik_sap',
        'nik_karyawan',
        'full_name',
        'email',
        'phone',
        'gender',
        'religion',
        'birth_place',
        'birth_date',
        'hire_date',
        'address',
        'position_id',
        'employment_status_id',
        'employee_grade',
        'marital_status',
        'dependent_count',
        'work_unit',
        'lvl_bod',
        'last_education',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'hire_date' => 'date',
            'dependent_count' => 'integer',
            'lvl_bod' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function getDependentCodeAttribute(): ?string
    {
        if (blank($this->marital_status)) {
            return null;
        }

        return sprintf('%s/%02d', $this->marital_status, $this->dependent_count ?? 0);
    }

    public function awardDateForYears(int $years): ?CarbonInterface
    {
        return $this->hire_date?->copy()
            ->addYears($years)
            ->addMonth()
            ->startOfMonth();
    }

    public function getHasImportWarningsAttribute(): bool
    {
        return blank($this->full_name)
            || blank($this->position_id)
            || blank($this->employment_status_id)
            || blank($this->work_unit)
            || blank($this->lvl_bod)
            || blank($this->hire_date);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function employmentStatus(): BelongsTo
    {
        return $this->belongsTo(EmploymentStatus::class);
    }

    public function families(): HasMany
    {
        return $this->hasMany(EmployeeFamily::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(EmployeeHistory::class);
    }
}
