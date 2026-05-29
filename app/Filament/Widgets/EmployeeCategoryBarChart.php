<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Widgets\ChartWidget;

class EmployeeCategoryBarChart extends ChartWidget
{
    protected static ?int $sort = -7;

    protected ?string $heading = 'Perbandingan Karyawan per Divisi';

    protected int|string|array $columnSpan = [
        'lg' => 1,
        'xl' => 1,
    ];

    protected function getData(): array
    {
        $divisionCounts = Employee::query()
            ->selectRaw('divisions.name as division_name, count(*) as aggregate')
            ->join('divisions', 'divisions.id', '=', 'employees.division_id')
            ->groupBy('divisions.name')
            ->pluck('aggregate', 'division_name');

        if ($divisionCounts->isEmpty()) {
            $divisionCounts = collect([
                'HR' => 10,
                'Finance' => 6,
                'Operations' => 8,
                'Technology' => 14,
            ]);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Karyawan',
                    'data' => $divisionCounts->values()->all(),
                    'backgroundColor' => '#f59e0b',
                    'borderRadius' => 8,
                ],
            ],
            'labels' => $divisionCounts->keys()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
