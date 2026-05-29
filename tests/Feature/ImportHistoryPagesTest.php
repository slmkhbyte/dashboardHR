<?php

namespace Tests\Feature;

use App\Filament\Imports\EmployeeFamilyImporter;
use App\Filament\Imports\EmployeeImporter;
use App\Models\User;
use Filament\Actions\Imports\Models\FailedImportRow;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportHistoryPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_import_history_only_shows_employee_imports(): void
    {
        $user = User::factory()->create();

        Import::query()->create([
            'file_name' => 'employee-import.csv',
            'file_path' => 'imports/employee-import.csv',
            'importer' => EmployeeImporter::class,
            'processed_rows' => 4,
            'total_rows' => 4,
            'successful_rows' => 4,
            'completed_at' => now(),
            'user_id' => $user->id,
        ]);

        Import::query()->create([
            'file_name' => 'family-import.csv',
            'file_path' => 'imports/family-import.csv',
            'importer' => EmployeeFamilyImporter::class,
            'processed_rows' => 2,
            'total_rows' => 2,
            'successful_rows' => 2,
            'completed_at' => now(),
            'user_id' => $user->id,
        ]);

        $this
            ->actingAs($user)
            ->get('/admin/employees/imports')
            ->assertOk()
            ->assertSee('employee-import.csv')
            ->assertDontSee('family-import.csv');
    }

    public function test_employee_failed_import_rows_page_renders_error_details(): void
    {
        $user = User::factory()->create();

        $import = Import::query()->create([
            'file_name' => 'employee-import.csv',
            'file_path' => 'imports/employee-import.csv',
            'importer' => EmployeeImporter::class,
            'processed_rows' => 3,
            'total_rows' => 3,
            'successful_rows' => 2,
            'completed_at' => now(),
            'user_id' => $user->id,
        ]);

        FailedImportRow::query()->create([
            'import_id' => $import->id,
            'data' => [
                'nik' => 'EMP-999',
                'full_name' => 'Baris Gagal',
            ],
            'validation_error' => 'The full name field is required.',
        ]);

        $this
            ->actingAs($user)
            ->get("/admin/employees/imports/{$import->id}/failed-rows")
            ->assertOk()
            ->assertSee('The full name field is required.')
            ->assertSee('EMP-999')
            ->assertSee('Baris Gagal');
    }
}
