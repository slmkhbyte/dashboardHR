<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmploymentStatus;
use App\Models\Position;
use App\Models\User;
use App\Support\EmployeeDocumentImageStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeDocumentImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_document_image_storage_builds_database_payload(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('tmp/employee-document-images/test__ktp.png', 'image-binary-data');

        $payload = EmployeeDocumentImageStorage::buildDatabasePayload('tmp/employee-document-images/test__ktp.png');

        $this->assertArrayHasKey('image_blob', $payload);
        $this->assertSame('ktp.png', $payload['image_original_filename']);
        $this->assertSame(strlen('image-binary-data'), $payload['image_size_bytes']);
    }

    public function test_employee_document_image_route_streams_blob_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $document = $this->createDocument([
            'image_blob' => 'binary-image-data',
            'image_mime_type' => 'image/png',
            'image_original_filename' => 'ktp.png',
            'image_size_bytes' => strlen('binary-image-data'),
        ]);

        $response = $this->actingAs($user)
            ->get(route('employee-documents.image', $document));

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('Content-Disposition', 'inline; filename="ktp.png"')
            ->assertHeader('Pragma', 'no-cache')
            ->assertHeader('Expires', '0')
            ->assertContent('binary-image-data');

        $cacheControl = $response->headers->get('Cache-Control', '');

        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('no-cache', $cacheControl);
        $this->assertStringContainsString('must-revalidate', $cacheControl);
        $this->assertStringContainsString('max-age=0', $cacheControl);
    }

    public function test_employee_document_image_route_can_force_download(): void
    {
        $user = User::factory()->create();
        $document = $this->createDocument([
            'image_blob' => 'binary-image-data',
            'image_mime_type' => 'image/png',
            'image_original_filename' => 'ktp.png',
            'image_size_bytes' => strlen('binary-image-data'),
        ]);

        $this->actingAs($user)
            ->get(route('employee-documents.image', [
                'employeeDocument' => $document,
                'download' => 1,
            ]))
            ->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename="ktp.png"');
    }

    public function test_employee_document_image_route_rejects_guest(): void
    {
        $document = $this->createDocument([
            'image_blob' => 'binary-image-data',
            'image_mime_type' => 'image/png',
            'image_original_filename' => 'ktp.png',
            'image_size_bytes' => strlen('binary-image-data'),
        ]);

        $this->get(route('employee-documents.image', $document))
            ->assertForbidden();
    }

    public function test_employee_document_image_storage_decodes_pgsql_resource_streams(): void
    {
        $binary = "\x89PNG\r\n\x1a\nbinary";
        $encoded = \App\Support\HguMarkerPhotoStorage::encodeBlobForDatabase($binary, 'pgsql');
        $stream = fopen('php://temp', 'rb+');

        fwrite($stream, $encoded);
        rewind($stream);

        $this->assertSame($binary, EmployeeDocumentImageStorage::decodeBlobFromDatabase($stream, 'pgsql'));

        fclose($stream);
    }

    public function test_employee_document_image_urls_include_cache_busting_version(): void
    {
        $document = $this->createDocument([
            'image_blob' => 'binary-image-data',
            'image_mime_type' => 'image/png',
            'image_original_filename' => 'ktp.png',
            'image_size_bytes' => strlen('binary-image-data'),
        ]);

        $this->assertNotNull($document->image_url);
        $this->assertStringContainsString('v=', $document->image_url);
        $this->assertStringContainsString('download=1', $document->image_download_url ?? '');
        $this->assertStringContainsString('v=', $document->image_download_url ?? '');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createDocument(array $attributes = []): EmployeeDocument
    {
        $position = Position::query()->create([
            'name' => 'Staff',
            'code' => 'STF',
            'is_active' => true,
        ]);

        $status = EmploymentStatus::query()->create([
            'name' => 'Tetap',
            'color' => 'success',
            'is_active' => true,
        ]);

        $employee = Employee::query()->create([
            'nik_sap' => fake()->unique()->numerify('########'),
            'nik_karyawan' => fake()->unique()->numerify('################'),
            'full_name' => fake()->name(),
            'hire_date' => now()->toDateString(),
            'position_id' => $position->id,
            'employment_status_id' => $status->id,
            'is_active' => true,
        ]);

        return EmployeeDocument::query()->create(array_merge([
            'employee_id' => $employee->id,
            'document_name' => 'KTP',
            'document_type' => 'Identitas',
            'status' => 'complete',
        ], $attributes));
    }
}
