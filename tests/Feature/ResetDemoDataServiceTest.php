<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmploymentStatus;
use App\Models\HguMarker;
use App\Models\HguMarkerHistory;
use App\Models\Position;
use App\Models\User;
use App\Support\ResetDemoDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResetDemoDataServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_resets_demo_data_back_to_baseline(): void
    {
        $user = User::factory()->create(['email' => 'existing@example.com']);

        Position::query()->create([
            'name' => 'Mandor Lapangan',
            'code' => 'MDL',
            'is_active' => true,
        ]);

        EmploymentStatus::query()->create([
            'name' => 'Magang',
            'color' => 'gray',
            'is_active' => true,
        ]);

        $result = app(ResetDemoDataService::class)->execute($user->id);

        $this->assertGreaterThan(0, $result['cleared']['total_deleted']);
        $this->assertSame(24, $result['seeded']['employees']);
        $this->assertGreaterThan(0, $result['seeded']['employee_families']);
        $this->assertGreaterThan(0, $result['seeded']['employee_documents']);
        $this->assertSame(6, $result['seeded']['hgu_markers']);

        $this->assertDatabaseHas('users', ['email' => 'existing@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'salma@dbgunme.com']);
        $this->assertDatabaseHas('positions', ['name' => Position::DEFAULT_NAME]);
        $this->assertDatabaseHas('employment_statuses', ['name' => EmploymentStatus::DEFAULT_NAME]);
        $this->assertDatabaseMissing('positions', ['name' => 'Mandor Lapangan']);
        $this->assertDatabaseMissing('employment_statuses', ['name' => 'Magang']);
        $this->assertDatabaseCount('employees', 24);
        $this->assertDatabaseCount('hgu_markers', 6);
        $this->assertDatabaseCount('hgu_marker_histories', 0);
        $this->assertDatabaseHas('hgu_markers', [
            'marker_number' => '103',
            'marker_type' => 'Pt.Kayu',
        ]);
    }

    public function test_it_is_safe_to_run_multiple_times_without_duplication(): void
    {
        app(ResetDemoDataService::class)->execute();
        $firstEmployeeCount = Employee::query()->count();
        $firstMarkerCount = HguMarker::query()->count();

        app(ResetDemoDataService::class)->execute();

        $this->assertSame($firstEmployeeCount, Employee::query()->count());
        $this->assertSame($firstMarkerCount, HguMarker::query()->count());
        $this->assertSame(0, HguMarkerHistory::query()->count());
        $this->assertSame(1, User::query()->where('email', 'salma@dbgunme.com')->count());
    }
}
