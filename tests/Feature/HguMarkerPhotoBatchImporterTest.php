<?php

namespace Tests\Feature;

use App\Models\HguMarker;
use App\Support\HguMarkerPhotoBatchImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HguMarkerPhotoBatchImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_batch_photo_import_matches_file_names_to_marker_numbers(): void
    {
        HguMarker::query()->create([
            'marker_number' => '1',
            'marker_type' => 'beton',
            'condition' => 'baik',
        ]);

        HguMarker::query()->create([
            'marker_number' => 'A-12',
            'marker_type' => 'paralon',
            'condition' => 'baik',
        ]);

        $result = app(HguMarkerPhotoBatchImporter::class)->import([
            'hgu-marker-photos/batch-1__001.jpg',
            'hgu-marker-photos/batch-2__A-12.png',
            'hgu-marker-photos/batch-3__999.jpg',
        ]);

        $this->assertSame(2, $result['created']);
        $this->assertSame(['batch-3__999.jpg'], $result['skipped']);

        $this->assertDatabaseHas('hgu_marker_photos', [
            'photo_path' => 'hgu-marker-photos/batch-1__001.jpg',
        ]);

        $this->assertDatabaseHas('hgu_marker_photos', [
            'photo_path' => 'hgu-marker-photos/batch-2__A-12.png',
        ]);
    }
}
