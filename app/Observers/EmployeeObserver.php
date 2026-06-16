<?php

namespace App\Observers;

use App\Models\Employee;
use App\Services\EmployeeSap\EmployeeSapFieldMap;
use Illuminate\Support\Arr;

class EmployeeObserver
{
    /**
     * Attributes that are not useful in the human-facing change log.
     *
     * @var array<int, string>
     */
    private array $ignoredAttributes = [
        'created_at',
        'updated_at',
    ];

    public function created(Employee $employee): void
    {
        $newValues = $this->cleanValues($employee->getAttributes());

        $this->recordHistory($employee, 'created', null, $newValues, array_keys($newValues));
    }

    public function updated(Employee $employee): void
    {
        $changes = Arr::except($employee->getChanges(), $this->ignoredAttributes);

        if ($changes === []) {
            return;
        }

        $oldValues = [];

        foreach (array_keys($changes) as $attribute) {
            $oldValues[$attribute] = $employee->getOriginal($attribute);
        }

        $this->recordHistory($employee, 'updated', $oldValues, $changes, array_keys($changes));
    }

    public function deleting(Employee $employee): void
    {
        $oldValues = $this->cleanValues($employee->getOriginal());

        $this->recordHistory($employee, 'deleted', $oldValues, null, array_keys($oldValues));
    }

    private function recordHistory(Employee $employee, string $event, ?array $oldValues, ?array $newValues, array $changedFields): void
    {
        $jobFields = array_keys(EmployeeSapFieldMap::jobTrackedEmployeeFields());
        $changedJobFields = array_values(array_intersect($changedFields, $jobFields));

        $employee->histories()->create([
            'event' => $event,
            'is_job_change' => $changedJobFields !== [],
            'changed_fields' => $changedFields,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'changed_by' => auth()->id(),
        ]);
    }

    private function cleanValues(array $values): array
    {
        return Arr::except($values, $this->ignoredAttributes);
    }
}
