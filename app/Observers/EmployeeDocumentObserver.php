<?php

namespace App\Observers;

use App\Models\EmployeeDocument;
use Illuminate\Support\Arr;

class EmployeeDocumentObserver
{
    /**
     * @var array<int, string>
     */
    private array $ignoredAttributes = [
        'created_at',
        'updated_at',
    ];

    public function created(EmployeeDocument $employeeDocument): void
    {
        $this->recordHistory(
            $employeeDocument,
            'created',
            null,
            $this->cleanValues($employeeDocument->getAttributes()),
            null,
            $this->extractImageSnapshot($employeeDocument->getAttributes(), 'image_'),
        );
    }

    public function updated(EmployeeDocument $employeeDocument): void
    {
        $changes = Arr::except($employeeDocument->getChanges(), $this->ignoredAttributes);

        if ($changes === []) {
            return;
        }

        $oldValues = [];

        foreach (array_keys($changes) as $attribute) {
            $oldValues[$attribute] = $employeeDocument->getOriginal($attribute);
        }

        $oldImageSnapshot = $this->hasImageChanges($changes)
            ? $this->extractImageSnapshot($oldValues, 'image_')
            : null;

        $newImageSnapshot = $this->hasImageChanges($changes)
            ? $this->extractImageSnapshot($changes, 'image_')
            : null;

        $this->recordHistory(
            $employeeDocument,
            'updated',
            $this->cleanValues($oldValues),
            $this->cleanValues($changes),
            $oldImageSnapshot,
            $newImageSnapshot,
        );
    }

    public function deleting(EmployeeDocument $employeeDocument): void
    {
        $this->recordHistory(
            $employeeDocument,
            'deleted',
            $this->cleanValues($employeeDocument->getOriginal()),
            null,
            $this->extractImageSnapshot($employeeDocument->getOriginal(), 'image_'),
            null,
        );
    }

    /**
     * @param  array<string, mixed>|null  $oldImage
     * @param  array<string, mixed>|null  $newImage
     */
    private function recordHistory(
        EmployeeDocument $employeeDocument,
        string $event,
        ?array $oldValues,
        ?array $newValues,
        ?array $oldImage = null,
        ?array $newImage = null,
    ): void {
        $employeeDocument->histories()->create(array_merge([
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'changed_by' => auth()->id(),
        ], $this->prefixImageAttributes($oldImage, 'old_'), $this->prefixImageAttributes($newImage, 'new_')));
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function cleanValues(array $values): array
    {
        return Arr::except($values, array_merge($this->ignoredAttributes, [
            'image_blob',
            'image_mime_type',
            'image_original_filename',
            'image_size_bytes',
        ]));
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>|null
     */
    private function extractImageSnapshot(array $values, string $prefix): ?array
    {
        $imageValues = Arr::only($values, [
            "{$prefix}blob",
            "{$prefix}mime_type",
            "{$prefix}original_filename",
            "{$prefix}size_bytes",
        ]);

        if (($imageValues["{$prefix}blob"] ?? null) === null) {
            return null;
        }

        return [
            'image_blob' => $imageValues["{$prefix}blob"] ?? null,
            'image_mime_type' => $imageValues["{$prefix}mime_type"] ?? null,
            'image_original_filename' => $imageValues["{$prefix}original_filename"] ?? null,
            'image_size_bytes' => $imageValues["{$prefix}size_bytes"] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $image
     * @return array<string, mixed>
     */
    private function prefixImageAttributes(?array $image, string $prefix): array
    {
        if ($image === null) {
            return [];
        }

        return [
            "{$prefix}image_blob" => $image['image_blob'] ?? null,
            "{$prefix}image_mime_type" => $image['image_mime_type'] ?? null,
            "{$prefix}image_original_filename" => $image['image_original_filename'] ?? null,
            "{$prefix}image_size_bytes" => $image['image_size_bytes'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    private function hasImageChanges(array $changes): bool
    {
        return Arr::hasAny($changes, [
            'image_blob',
            'image_mime_type',
            'image_original_filename',
            'image_size_bytes',
        ]);
    }
}
