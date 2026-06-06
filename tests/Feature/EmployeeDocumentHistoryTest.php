<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmployeeDocumentHistory;
use App\Models\EmploymentStatus;
use App\Models\Position;
use App\Models\User;
use App\Support\HguMarkerPhotoStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeDocumentHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_document_created_event_creates_history_snapshot(): void
    {
        $document = $this->createDocument([
            'document_name' => 'KTP Aktif',
            'image_blob' => 'created-image',
            'image_mime_type' => 'image/png',
            'image_original_filename' => 'ktp-created.png',
            'image_size_bytes' => strlen('created-image'),
        ]);

        $history = EmployeeDocumentHistory::query()->where('employee_document_id', $document->id)->firstOrFail();

        $this->assertSame('created', $history->event);
        $this->assertNull($history->old_values);
        $this->assertSame('KTP Aktif', $history->new_values['document_name'] ?? null);
        $this->assertSame('ktp-created.png', $history->new_image_original_filename);
    }

    public function test_employee_document_update_keeps_old_image_in_history(): void
    {
        $document = $this->createDocument([
            'document_name' => 'KTP Lama',
            'image_blob' => 'old-image',
            'image_mime_type' => 'image/png',
            'image_original_filename' => 'ktp-old.png',
            'image_size_bytes' => strlen('old-image'),
        ]);

        $document->update([
            'document_name' => 'KTP Baru',
            'image_blob' => 'new-image',
            'image_mime_type' => 'image/webp',
            'image_original_filename' => 'ktp-new.webp',
            'image_size_bytes' => strlen('new-image'),
        ]);

        $history = EmployeeDocumentHistory::query()
            ->where('employee_document_id', $document->id)
            ->where('event', 'updated')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame('KTP Lama', $history->old_values['document_name'] ?? null);
        $this->assertSame('KTP Baru', $history->new_values['document_name'] ?? null);
        $this->assertSame('ktp-old.png', $history->old_image_original_filename);
        $this->assertSame('ktp-new.webp', $history->new_image_original_filename);
    }

    public function test_employee_document_history_image_route_streams_old_and_new_images_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $history = EmployeeDocumentHistory::query()->create([
            'employee_document_id' => $this->createDocument()->id,
            'event' => 'updated',
            'old_values' => ['document_name' => 'KTP Lama'],
            'new_values' => ['document_name' => 'KTP Baru'],
            'old_image_blob' => 'old-image',
            'old_image_mime_type' => 'image/png',
            'old_image_original_filename' => 'ktp-old.png',
            'old_image_size_bytes' => strlen('old-image'),
            'new_image_blob' => 'new-image',
            'new_image_mime_type' => 'image/webp',
            'new_image_original_filename' => 'ktp-new.webp',
            'new_image_size_bytes' => strlen('new-image'),
        ]);

        $this->actingAs($user)
            ->get(route('employee-document-histories.image', [
                'employeeDocumentHistory' => $history,
                'version' => 'old',
            ]))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertContent('old-image');

        $this->actingAs($user)
            ->get(route('employee-document-histories.image', [
                'employeeDocumentHistory' => $history,
                'version' => 'new',
                'download' => 1,
            ]))
            ->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename="ktp-new.webp"')
            ->assertContent('new-image');
    }

    public function test_employee_document_history_image_route_rejects_guest(): void
    {
        $history = EmployeeDocumentHistory::query()->create([
            'employee_document_id' => $this->createDocument()->id,
            'event' => 'updated',
            'old_image_blob' => 'old-image',
            'old_image_mime_type' => 'image/png',
            'old_image_original_filename' => 'ktp-old.png',
            'old_image_size_bytes' => strlen('old-image'),
        ]);

        $this->get(route('employee-document-histories.image', [
            'employeeDocumentHistory' => $history,
            'version' => 'old',
        ]))->assertForbidden();
    }

    public function test_employee_document_history_decodes_pgsql_resource_streams(): void
    {
        $binary = "\x89PNG\r\n\x1a\nbinary";
        $encoded = HguMarkerPhotoStorage::encodeBlobForDatabase($binary, 'pgsql');
        $stream = fopen('php://temp', 'rb+');

        fwrite($stream, $encoded);
        rewind($stream);

        $this->assertSame($binary, \App\Support\EmployeeDocumentImageStorage::decodeBlobFromDatabase($stream, 'pgsql'));

        fclose($stream);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createDocument(array $attributes = []): EmployeeDocument
    {
        $position = Position::query()->create([
            'name' => 'Staff ' . fake()->unique()->numerify('###'),
            'code' => 'ST' . fake()->unique()->numerify('##'),
            'is_active' => true,
        ]);

        $status = EmploymentStatus::query()->create([
            'name' => 'Tetap ' . fake()->unique()->numerify('###'),
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
