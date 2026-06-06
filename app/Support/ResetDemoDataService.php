<?php

namespace App\Support;

use Database\Seeders\DemoDataSeeder;
use Illuminate\Support\Facades\Log;

class ResetDemoDataService
{
    /**
     * @return array{
     *     cleared: array{
     *         hr: array<string, int>,
     *         hgu: array<string, int>,
     *         imports_exports: array<string, int>,
     *         master_hr: array<string, int>,
     *         total_deleted: int
     *     },
     *     seeded: array{
     *         positions: int,
     *         employment_statuses: int,
     *         employees: int,
     *         employee_families: int,
     *         employee_documents: int,
     *         hgu_markers: int
     *     }
     * }
     */
    public function execute(?int $performedBy = null): array
    {
        $cleared = app(ClearDemoDataService::class)->execute($performedBy, includeMasterHr: true);

        app(DemoDataSeeder::class)->run();

        $seeded = [
            'positions' => \App\Models\Position::query()->count(),
            'employment_statuses' => \App\Models\EmploymentStatus::query()->count(),
            'employees' => \App\Models\Employee::query()->count(),
            'employee_families' => \App\Models\EmployeeFamily::query()->count(),
            'employee_documents' => \App\Models\EmployeeDocument::query()->count(),
            'hgu_markers' => \App\Models\HguMarker::query()->count(),
        ];

        Log::info('Demo data reset from dashboard action.', [
            'performed_by' => $performedBy,
            'cleared' => $cleared,
            'seeded' => $seeded,
        ]);

        return [
            'cleared' => $cleared,
            'seeded' => $seeded,
        ];
    }
}
