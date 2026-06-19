<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\EmployeeSapSnapshot;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EmployeeSapWorkUnitComparison extends Widget
{
    protected static ?int $sort = -5;

    protected string $view = 'filament.widgets.employee-sap-work-unit-comparison';

    protected ?string $pollingInterval = '10s';

    protected int|string|array $columnSpan = 'full';

    private const POSITIONS = [
        'PEMANEN' => 'PEMANEN',
        'PEMELIHARAAN' => 'PEMELIHARAAN',
    ];

    private const STATUSES = [
        'karpel_tetap' => 'Karpel-Tetap',
        'ktng' => 'KTNG',
        'pkwt' => 'PKWT',
    ];

    /**
     * @return array<int, array{
     *     work_unit: string,
     *     positions: array<string, array{
     *         local: array<string, int>,
     *         sap: array<string, int>
     *     }>
     * }>
     */
    public function getComparisonRows(): array
    {
        $latestSnapshot = $this->getLatestSnapshot();
        $localCounts = $this->getLocalCounts();
        $sapCounts = $latestSnapshot === null
            ? collect()
            : $this->getSapCounts($latestSnapshot);

        return $localCounts
            ->keys()
            ->merge($sapCounts->keys())
            ->unique()
            ->sort()
            ->values()
            ->map(fn (string $workUnit): array => [
                'work_unit' => $workUnit,
                'positions' => collect(self::POSITIONS)
                    ->mapWithKeys(fn (string $position): array => [
                        $position => [
                            'local' => $this->emptyStatusCounts($localCounts->get($workUnit, collect())->get($position, collect())),
                            'sap' => $this->emptyStatusCounts($sapCounts->get($workUnit, collect())->get($position, collect())),
                        ],
                    ])
                    ->all(),
            ])
            ->all();
    }

    public function getLatestSnapshotLabel(): ?string
    {
        return $this->getLatestSnapshot()?->period_label;
    }

    private function getLatestSnapshot(): ?EmployeeSapSnapshot
    {
        return EmployeeSapSnapshot::query()
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->orderByDesc('imported_at')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @return Collection<string, Collection<string, Collection<string, int>>>
     */
    private function getLocalCounts(): Collection
    {
        $targetPositions = array_keys(self::POSITIONS);
        $targetStatuses = array_values(self::STATUSES);

        return Employee::query()
            ->active()
            ->select([
                'employees.work_unit',
                'positions.name as position_name',
                'employment_statuses.name as status_name',
            ])
            ->join('positions', 'positions.id', '=', 'employees.position_id')
            ->join('employment_statuses', 'employment_statuses.id', '=', 'employees.employment_status_id')
            ->whereNotNull('employees.work_unit')
            ->whereRaw('LOWER(employees.work_unit) LIKE ?', ['%afdeling%'])
            ->whereIn('employment_statuses.name', $targetStatuses)
            ->whereIn(DB::raw('LOWER(positions.name)'), array_map('strtolower', $targetPositions))
            ->get()
            ->reduce(
                fn (Collection $counts, Employee $employee): Collection => $this->addCount(
                    $counts,
                    $employee->work_unit,
                    $employee->position_name,
                    $employee->status_name,
                ),
                collect(),
            );
    }

    /**
     * @return Collection<string, Collection<string, Collection<string, int>>>
     */
    private function getSapCounts(EmployeeSapSnapshot $snapshot): Collection
    {
        $targetPositions = array_keys(self::POSITIONS);
        $targetStatuses = array_values(self::STATUSES);

        return $snapshot->rows()
            ->select([
                'work_unit',
                'position',
                'employment_status',
            ])
            ->whereNotNull('work_unit')
            ->whereRaw('LOWER(work_unit) LIKE ?', ['%afdeling%'])
            ->where(function ($query): void {
                $query
                    ->where('is_active', true)
                    ->orWhereNull('is_active');
            })
            ->whereIn('employment_status', $targetStatuses)
            ->whereIn(DB::raw('LOWER(position)'), array_map('strtolower', $targetPositions))
            ->get()
            ->reduce(
                fn (Collection $counts, $row): Collection => $this->addCount(
                    $counts,
                    $row->work_unit,
                    $row->position,
                    $row->employment_status,
                ),
                collect(),
            );
    }

    private function addCount(Collection $counts, ?string $workUnit, ?string $position, ?string $status): Collection
    {
        $workUnitKey = $this->normalizeWorkUnit($workUnit);
        $positionKey = $this->normalizePosition($position);

        if ($workUnitKey === null || $positionKey === null || ! in_array($status, self::STATUSES, true)) {
            return $counts;
        }

        $current = $counts->get($workUnitKey, collect())
            ->get($positionKey, collect())
            ->get($status, 0);

        $counts->put(
            $workUnitKey,
            $counts->get($workUnitKey, collect())->put(
                $positionKey,
                $counts->get($workUnitKey, collect())->get($positionKey, collect())->put($status, $current + 1),
            ),
        );

        return $counts;
    }

    /**
     * @param  Collection<string, int>|null  $counts
     * @return array{karpel_tetap: int, ktng: int, pkwt: int, total: int}
     */
    private function emptyStatusCounts(?Collection $counts): array
    {
        $values = collect(self::STATUSES)
            ->mapWithKeys(fn (string $status, string $key): array => [$key => (int) ($counts?->get($status) ?? 0)])
            ->all();

        return $values + [
            'total' => array_sum($values),
        ];
    }

    private function normalizeWorkUnit(?string $workUnit): ?string
    {
        if (blank($workUnit)) {
            return null;
        }

        return mb_strtoupper(trim($workUnit));
    }

    private function normalizePosition(?string $position): ?string
    {
        if (blank($position)) {
            return null;
        }

        $position = mb_strtoupper(trim($position));

        return self::POSITIONS[$position] ?? null;
    }
}
