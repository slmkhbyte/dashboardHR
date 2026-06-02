<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Widgets\ChartWidget;

class EmployeeCategoryBarChart extends ChartWidget
{
    protected static ?int $sort = -7;

    protected ?string $heading = 'Perbandingan Karyawan per Work Unit';

    protected int|string|array $columnSpan = [
        'lg' => 1,
        'xl' => 1,
    ];

    protected function getData(): array
    {
        $workUnitCounts = Employee::query()
            ->selectRaw('work_unit, count(*) as aggregate')
            ->whereNotNull('work_unit')
            ->groupBy('work_unit')
            ->pluck('aggregate', 'work_unit');

        if ($workUnitCounts->isEmpty()) {
            $workUnitCounts = collect([
                'AFDELING I' => 10,
                'AFDELING II' => 6,
                'AFDELING III' => 8,
                'AFDELING IV' => 14,
            ]);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Karyawan',
                    'data' => $workUnitCounts->values()->all(),
                    'backgroundColor' => '#f59e0b',
                    'borderRadius' => 8,
                ],
            ],
            'labels' => $workUnitCounts->keys()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
