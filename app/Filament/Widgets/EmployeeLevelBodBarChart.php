<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Widgets\ChartWidget;

class EmployeeLevelBodBarChart extends ChartWidget
{
    protected static ?int $sort = -6;

    protected ?string $heading = 'Jumlah Karyawan per LVL BOD';

    protected int|string|array $columnSpan = [
        'lg' => 1,
        'xl' => 1,
    ];

    protected function getData(): array
    {
        $levelCounts = Employee::query()
            ->active()
            ->selectRaw('lvl_bod, count(*) as aggregate')
            ->whereNotNull('lvl_bod')
            ->groupBy('lvl_bod')
            ->orderBy('lvl_bod')
            ->pluck('aggregate', 'lvl_bod');

        if ($levelCounts->isEmpty()) {
            $levelCounts = collect([
                '1' => 4,
                '2' => 8,
                '3' => 9,
                '4' => 12,
            ]);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Karyawan',
                    'data' => $levelCounts->values()->all(),
                    'backgroundColor' => '#22c55e',
                    'borderRadius' => 8,
                ],
            ],
            'labels' => $levelCounts->keys()->map(fn ($key) => (string) $key)->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
