<?php

namespace App\Support;

use App\Models\EmploymentStatus;
use App\Models\Position;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClearDemoDataService
{
    /**
     * @return array{
     *     hr: array<string, int>,
     *     hgu: array<string, int>,
     *     imports_exports: array<string, int>,
     *     master_hr: array<string, int>,
     *     total_deleted: int
     * }
     */
    public function execute(?int $performedBy = null, bool $includeMasterHr = false): array
    {
        $summary = DB::transaction(function () use ($includeMasterHr): array {
            $hr = [
                'employee_document_histories' => DB::table('employee_document_histories')->delete(),
                'employee_documents' => DB::table('employee_documents')->delete(),
                'employee_families' => DB::table('employee_families')->delete(),
                'employee_histories' => DB::table('employee_histories')->delete(),
                'employees' => DB::table('employees')->delete(),
            ];

            $hgu = [
                'hgu_marker_histories' => DB::table('hgu_marker_histories')->delete(),
                'hgu_marker_moves' => DB::table('hgu_marker_moves')->delete(),
                'hgu_marker_photos' => DB::table('hgu_marker_photos')->delete(),
                'hgu_markers' => DB::table('hgu_markers')->delete(),
            ];

            $importsExports = [
                'failed_import_rows' => DB::table('failed_import_rows')->delete(),
                'imports' => DB::table('imports')->delete(),
                'exports' => DB::table('exports')->delete(),
            ];

            $masterHr = [
                'positions' => 0,
                'employment_statuses' => 0,
            ];

            if ($includeMasterHr) {
                $masterHr['positions'] = Position::query()
                    ->where('name', '!=', Position::DEFAULT_NAME)
                    ->delete();

                $masterHr['employment_statuses'] = EmploymentStatus::query()
                    ->where('name', '!=', EmploymentStatus::DEFAULT_NAME)
                    ->delete();
            }

            return [
                'hr' => $hr,
                'hgu' => $hgu,
                'imports_exports' => $importsExports,
                'master_hr' => $masterHr,
                'total_deleted' => array_sum($hr) + array_sum($hgu) + array_sum($importsExports) + array_sum($masterHr),
            ];
        });

        Log::info('Demo data cleared from dashboard action.', [
            'performed_by' => $performedBy,
            'include_master_hr' => $includeMasterHr,
            'summary' => $summary,
        ]);

        return $summary;
    }
}
