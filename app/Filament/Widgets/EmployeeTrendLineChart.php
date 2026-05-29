<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Widgets\ChartWidget;

class EmployeeTrendLineChart extends ChartWidget
{
    protected static ?int $sort = -6;

    protected ?string $heading = 'Progres Penambahan Data per Bulan';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $labels = collect(range(5, 0))
            ->map(fn (int $monthsAgo): string => now()->subMonths($monthsAgo)->translatedFormat('M Y'));

        $series = Employee::query()
            ->get(['hire_date'])
            ->groupBy(fn (Employee $employee): string => $employee->hire_date->format('Y-m'));

        $points = collect(range(5, 0))
            ->map(fn (int $monthsAgo): int => $series->get(now()->subMonths($monthsAgo)->format('Y-m'))?->count() ?? 0);

        if ($points->sum() === 0) {
            $points = collect([4, 6, 7, 9, 11, 13]);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Karyawan Baru',
                    'data' => $points->all(),
                    'borderColor' => '#f97316',
                    'backgroundColor' => 'rgba(249, 115, 22, 0.18)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
