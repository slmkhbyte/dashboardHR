<?php

namespace Tests\Feature;

use App\Models\HguMarker;
use App\Models\HguMarkerPhoto;
use App\Models\User;
use App\Support\HguMarkerPhotoBatchImporter;
use App\Support\HguMarkerPhotoStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HguMarkerPhotoBatchImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_batch_photo_import_matches_file_names_to_marker_numbers(): void
    {
        Storage::fake('local');

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

        Storage::disk('local')->put('tmp/hgu-marker-photos/batch-1__001.jpg', 'image-one');
        Storage::disk('local')->put('tmp/hgu-marker-photos/batch-2__A-12.png', 'image-two');
        Storage::disk('local')->put('tmp/hgu-marker-photos/batch-3__999.jpg', 'image-three');

        $result = app(HguMarkerPhotoBatchImporter::class)->import([
            'tmp/hgu-marker-photos/batch-1__001.jpg',
            'tmp/hgu-marker-photos/batch-2__A-12.png',
            'tmp/hgu-marker-photos/batch-3__999.jpg',
        ]);

        $this->assertSame(2, $result['created']);
        $this->assertSame(['batch-3__999.jpg'], $result['skipped']);

        $this->assertDatabaseHas('hgu_marker_photos', [
            'photo_path' => 'db://tmp/hgu-marker-photos/batch-1__001.jpg',
            'original_filename' => '001.jpg',
        ]);

        $this->assertDatabaseHas('hgu_marker_photos', [
            'photo_path' => 'db://tmp/hgu-marker-photos/batch-2__A-12.png',
            'original_filename' => 'A-12.png',
        ]);

        $this->assertDatabaseMissing('hgu_marker_photos', [
            'photo_path' => 'db://tmp/hgu-marker-photos/batch-3__999.jpg',
        ]);

        $this->assertFalse(Storage::disk('local')->exists('tmp/hgu-marker-photos/batch-1__001.jpg'));
        $this->assertFalse(Storage::disk('local')->exists('tmp/hgu-marker-photos/batch-2__A-12.png'));
    }

    public function test_hgu_marker_photo_image_route_streams_blob_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $photo = HguMarkerPhoto::query()->create([
            'hgu_marker_id' => HguMarker::query()->create([
                'marker_number' => '10',
                'marker_type' => 'beton',
                'condition' => 'baik',
            ])->id,
            'photo_path' => 'db://demo/photo.jpg',
            'photo_blob' => 'binary-image-data',
            'photo_mime_type' => 'image/jpeg',
            'original_filename' => 'photo.jpg',
            'photo_size_bytes' => strlen('binary-image-data'),
            'uploaded_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('hgu-marker-photos.show', $photo))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg')
            ->assertContent('binary-image-data');
    }

    public function test_hgu_marker_photo_image_route_rejects_guest(): void
    {
        $photo = HguMarkerPhoto::query()->create([
            'hgu_marker_id' => HguMarker::query()->create([
                'marker_number' => '11',
                'marker_type' => 'beton',
                'condition' => 'baik',
            ])->id,
            'photo_path' => 'db://demo/photo.jpg',
            'photo_blob' => 'binary-image-data',
            'photo_mime_type' => 'image/jpeg',
            'original_filename' => 'photo.jpg',
            'photo_size_bytes' => strlen('binary-image-data'),
            'uploaded_at' => now(),
        ]);

        $this->get(route('hgu-marker-photos.show', $photo))
            ->assertForbidden();
    }

    public function test_hgu_marker_photo_storage_encodes_and_decodes_pgsql_bytea_payloads(): void
    {
        $binary = "\x89PNG\r\n\x1a\nbinary";

        $encoded = HguMarkerPhotoStorage::encodeBlobForDatabase($binary, 'pgsql');

        $this->assertStringStartsWith('\\x', $encoded);
        $this->assertSame($binary, HguMarkerPhotoStorage::decodeBlobFromDatabase($encoded, 'pgsql'));
    }

    public function test_hgu_marker_photo_storage_decodes_pgsql_resource_streams(): void
    {
        $binary = "\x89PNG\r\n\x1a\nbinary";
        $encoded = HguMarkerPhotoStorage::encodeBlobForDatabase($binary, 'pgsql');
        $stream = fopen('php://temp', 'rb+');

        fwrite($stream, $encoded);
        rewind($stream);

        $this->assertSame($binary, HguMarkerPhotoStorage::decodeBlobFromDatabase($stream, 'pgsql'));

        fclose($stream);
    }
}
