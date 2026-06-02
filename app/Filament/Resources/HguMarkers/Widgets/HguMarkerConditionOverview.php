<?php

namespace App\Filament\Resources\HguMarkers\Widgets;

use App\Models\HguMarker;
use Filament\Widgets\ChartWidget;

class HguMarkerConditionOverview extends ChartWidget
{
    protected ?string $heading = 'Rekap Kondisi Patok per Afdeling';

    protected ?string $maxHeight = '320px';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $labels = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
        ];

        $counts = HguMarker::query()
            ->selectRaw('afdeling, condition, count(*) as aggregate')
            ->whereNotNull('afdeling')
            ->groupBy('afdeling', 'condition')
            ->get()
            ->groupBy('condition');

        return [
            'datasets' => collect(HguMarker::CONDITIONS)
                ->map(function (string $label, string $condition) use ($counts, $labels): array {
                    $conditionCounts = $counts->get($condition, collect())->keyBy('afdeling');

                    return [
                        'label' => $label,
                        'data' => collect(array_keys($labels))
                            ->map(fn (int $afdeling): int => (int) ($conditionCounts->get($afdeling)?->aggregate ?? 0))
                            ->values()
                            ->all(),
                        'backgroundColor' => match ($condition) {
                            'baik' => '#16a34a',
                            'rusak_ringan' => '#f59e0b',
                            'rusak_berat' => '#dc2626',
                            'hilang' => '#6b7280',
                            default => '#9ca3af',
                        },
                    ];
                })
                ->values()
                ->all(),
            'labels' => array_values($labels),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => [
                    'stacked' => true,
                ],
                'y' => [
                    'stacked' => true,
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
