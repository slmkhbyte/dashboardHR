<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Widgets\ChartWidget;

class EmployeeDistributionChart extends ChartWidget
{
    protected static ?int $sort = -8;

    protected ?string $heading = 'Distribusi Status Karyawan';

    protected ?string $pollingInterval = '10s';

    protected int|string|array $columnSpan = [
        'lg' => 1,
        'xl' => 1,
    ];

    protected function getData(): array
    {
        $statusCounts = Employee::query()
            ->active()
            ->selectRaw('employment_statuses.name as status_name, count(*) as aggregate')
            ->join('employment_statuses', 'employment_statuses.id', '=', 'employees.employment_status_id')
            ->groupBy('employment_statuses.name')
            ->pluck('aggregate', 'status_name');

        if ($statusCounts->isEmpty()) {
            $statusCounts = collect([
                'Tetap' => 12,
                'Kontrak' => 7,
                'Probation' => 4,
            ]);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Status',
                    'data' => $statusCounts->values()->all(),
                    'backgroundColor' => ['#16a34a', '#f59e0b', '#0ea5e9'],
                ],
            ],
            'labels' => $statusCounts->keys()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
