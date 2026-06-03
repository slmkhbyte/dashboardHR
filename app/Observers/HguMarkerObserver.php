<?php

namespace App\Observers;

use App\Models\HguMarker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Throwable;

class HguMarkerObserver
{
    /**
     * @var array<int, string>
     */
    private array $ignoredAttributes = [
        'created_at',
        'updated_at',
    ];

    public function created(HguMarker $marker): void
    {
        $this->recordHistory($marker, 'created', null, $this->cleanValues($marker->getAttributes()));
    }

    public function updated(HguMarker $marker): void
    {
        $changes = Arr::except($marker->getChanges(), $this->ignoredAttributes);

        if ($changes === []) {
            return;
        }

        $oldValues = [];

        foreach (array_keys($changes) as $attribute) {
            $oldValues[$attribute] = $marker->getOriginal($attribute);
        }

        $this->recordHistory($marker, 'updated', $oldValues, $changes);
    }

    public function deleting(HguMarker $marker): void
    {
        $this->recordHistory($marker, 'deleted', $this->cleanValues($marker->getOriginal()), null);
    }

    private function recordHistory(HguMarker $marker, string $event, ?array $oldValues, ?array $newValues): void
    {
        try {
            $marker->histories()->create([
                'event' => $event,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'changed_by' => auth()->id(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to write HGU marker history.', [
                'marker_id' => $marker->getKey(),
                'marker_number' => $marker->marker_number,
                'event' => $event,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'exception_class' => $exception::class,
                'exception_message' => $exception->getMessage(),
            ]);
        }
    }

    private function cleanValues(array $values): array
    {
        return Arr::except($values, $this->ignoredAttributes);
    }
}
