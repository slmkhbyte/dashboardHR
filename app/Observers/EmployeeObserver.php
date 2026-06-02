<?php

namespace App\Observers;

use App\Models\Employee;
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
        $this->recordHistory($employee, 'created', null, $this->cleanValues($employee->getAttributes()));
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

        $this->recordHistory($employee, 'updated', $oldValues, $changes);
    }

    public function deleting(Employee $employee): void
    {
        $this->recordHistory($employee, 'deleted', $this->cleanValues($employee->getOriginal()), null);
    }

    private function recordHistory(Employee $employee, string $event, ?array $oldValues, ?array $newValues): void
    {
        $employee->histories()->create([
            'event' => $event,
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
