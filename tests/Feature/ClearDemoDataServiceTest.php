<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmployeeDocumentHistory;
use App\Models\EmployeeFamily;
use App\Models\EmployeeHistory;
use App\Models\EmploymentStatus;
use App\Models\HguMarker;
use App\Models\HguMarkerHistory;
use App\Models\HguMarkerMove;
use App\Models\HguMarkerPhoto;
use App\Models\Position;
use App\Models\User;
use App\Support\ClearDemoDataService;
use Filament\Actions\Imports\Models\Import;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClearDemoDataServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_clears_only_business_and_demo_history_data(): void
    {
        $user = User::factory()->create();
        $position = Position::getOrCreateDefault();
        $employmentStatus = EmploymentStatus::getOrCreateDefault();

        $employee = Employee::query()->create([
            'nik_sap' => '13009999',
            'nik_karyawan' => '0000999900009999',
            'full_name' => 'Dummy Employee',
            'hire_date' => now()->startOfDay(),
            'position_id' => $position->id,
            'employment_status_id' => $employmentStatus->id,
            'is_active' => true,
        ]);

        EmployeeFamily::query()->create([
            'employee_id' => $employee->id,
            'name' => 'Dummy Family',
            'relationship' => 'Pasangan',
        ]);

        $document = EmployeeDocument::query()->create([
            'employee_id' => $employee->id,
            'document_name' => 'KTP',
            'document_type' => 'Identitas',
            'status' => 'complete',
        ]);

        EmployeeHistory::query()->create([
            'employee_id' => $employee->id,
            'event' => 'updated',
        ]);

        EmployeeDocumentHistory::query()->create([
            'employee_document_id' => $document->id,
            'event' => 'created',
        ]);

        $marker = HguMarker::query()->create([
            'marker_number' => '1',
        ]);

        HguMarkerPhoto::query()->create([
            'hgu_marker_id' => $marker->id,
            'photo_path' => 'db://dummy',
        ]);

        HguMarkerMove::query()->create([
            'hgu_marker_id' => $marker->id,
        ]);

        HguMarkerHistory::query()->create([
            'hgu_marker_id' => $marker->id,
            'event' => 'updated',
        ]);

        $import = Import::query()->create([
            'file_name' => 'dummy.csv',
            'file_path' => 'imports/dummy.csv',
            'importer' => 'DummyImporter',
            'processed_rows' => 1,
            'total_rows' => 1,
            'successful_rows' => 1,
            'user_id' => $user->id,
        ]);

        DB::table('failed_import_rows')->insert([
            'data' => json_encode(['row' => 1], JSON_THROW_ON_ERROR),
            'import_id' => $import->id,
            'validation_error' => 'Dummy error',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Export::query()->create([
            'file_disk' => 'local',
            'file_name' => 'dummy.csv',
            'exporter' => 'DummyExporter',
            'processed_rows' => 1,
            'total_rows' => 1,
            'successful_rows' => 1,
            'user_id' => $user->id,
        ]);

        $summary = app(ClearDemoDataService::class)->execute($user->id);

        $this->assertGreaterThan(0, $summary['total_deleted']);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('positions', 1);
        $this->assertDatabaseCount('employment_statuses', 1);
        $this->assertDatabaseCount('employees', 0);
        $this->assertDatabaseCount('employee_families', 0);
        $this->assertDatabaseCount('employee_documents', 0);
        $this->assertDatabaseCount('employee_histories', 0);
        $this->assertDatabaseCount('employee_document_histories', 0);
        $this->assertDatabaseCount('hgu_markers', 0);
        $this->assertDatabaseCount('hgu_marker_photos', 0);
        $this->assertDatabaseCount('hgu_marker_moves', 0);
        $this->assertDatabaseCount('hgu_marker_histories', 0);
        $this->assertDatabaseCount('imports', 0);
        $this->assertDatabaseCount('failed_import_rows', 0);
        $this->assertDatabaseCount('exports', 0);
    }

    public function test_it_is_safe_to_run_when_demo_data_is_already_empty(): void
    {
        User::factory()->create();
        Position::getOrCreateDefault();
        EmploymentStatus::getOrCreateDefault();

        $summary = app(ClearDemoDataService::class)->execute();

        $this->assertSame(0, $summary['total_deleted']);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('positions', 1);
        $this->assertDatabaseCount('employment_statuses', 1);
        $this->assertDatabaseCount('employees', 0);
        $this->assertDatabaseCount('hgu_markers', 0);
        $this->assertDatabaseCount('imports', 0);
        $this->assertDatabaseCount('exports', 0);
    }

    public function test_it_can_clear_business_data_and_non_default_master_hr_records(): void
    {
        User::factory()->create();

        $defaultPosition = Position::getOrCreateDefault();
        $customPosition = Position::query()->create([
            'name' => 'Mandor',
            'code' => 'MDR',
            'is_active' => true,
        ]);

        $defaultEmploymentStatus = EmploymentStatus::getOrCreateDefault();
        $customEmploymentStatus = EmploymentStatus::query()->create([
            'name' => 'Tetap',
            'color' => 'success',
            'is_active' => true,
        ]);

        $employee = Employee::query()->create([
            'nik_sap' => '13008888',
            'nik_karyawan' => '0000888800008888',
            'full_name' => 'Master Cleanup Employee',
            'hire_date' => now()->startOfDay(),
            'position_id' => $customPosition->id,
            'employment_status_id' => $customEmploymentStatus->id,
            'is_active' => true,
        ]);

        EmployeeHistory::query()->create([
            'employee_id' => $employee->id,
            'event' => 'updated',
        ]);

        $summary = app(ClearDemoDataService::class)->execute(includeMasterHr: true);

        $this->assertGreaterThanOrEqual(2, array_sum($summary['master_hr']));
        $this->assertDatabaseMissing('positions', ['id' => $customPosition->id]);
        $this->assertDatabaseMissing('employment_statuses', ['id' => $customEmploymentStatus->id]);
        $this->assertDatabaseHas('positions', ['id' => $defaultPosition->id, 'name' => Position::DEFAULT_NAME]);
        $this->assertDatabaseHas('employment_statuses', ['id' => $defaultEmploymentStatus->id, 'name' => EmploymentStatus::DEFAULT_NAME]);
        $this->assertDatabaseCount('employees', 0);
    }
}
