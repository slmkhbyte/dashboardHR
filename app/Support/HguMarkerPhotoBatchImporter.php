<?php

namespace App\Support;

use App\Models\HguMarker;
use App\Models\HguMarkerPhoto;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class HguMarkerPhotoBatchImporter
{
    /**
     * @param  array<int, string>  $paths
     * @return array{created: int, skipped: array<int, string>}
     */
    public function import(array $paths, ?int $uploadedBy = null): array
    {
        $markers = $this->getMarkerLookup();
        $created = 0;
        $skipped = [];

        foreach ($paths as $path) {
            $markerNumber = $this->markerNumberFromPath($path);
            $lookupKey = $this->normalizeMarkerNumber($markerNumber);
            $marker = $markers->get($lookupKey);

            if (! $marker) {
                $skipped[] = basename($path);

                continue;
            }

            HguMarkerPhoto::query()->create([
                'hgu_marker_id' => $marker->id,
                'photo_path' => $path,
                'caption' => 'Upload batch: ' . basename($path),
                'uploaded_at' => now(),
                'uploaded_by' => $uploadedBy,
            ]);

            $created++;
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
        ];
    }

    /**
     * @return Collection<string, HguMarker>
     */
    private function getMarkerLookup(): Collection
    {
        return HguMarker::query()
            ->get(['id', 'marker_number'])
            ->keyBy(fn (HguMarker $marker): string => $this->normalizeMarkerNumber($marker->marker_number));
    }

    private function markerNumberFromPath(string $path): string
    {
        $filename = pathinfo($path, PATHINFO_FILENAME);

        if (str_contains($filename, '__')) {
            $filename = Str::after($filename, '__');
        }

        return $filename;
    }

    private function normalizeMarkerNumber(string $markerNumber): string
    {
        $markerNumber = trim($markerNumber);

        if (preg_match('/^\d+$/', $markerNumber) === 1) {
            return (string) ((int) $markerNumber);
        }

        return Str::lower($markerNumber);
    }
}
