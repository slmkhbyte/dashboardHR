<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmployeeFamily;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HrStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = -9;

    protected ?string $pollingInterval = '10s';

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Karyawan', (string) Employee::query()->active()->count())
                ->description('Data karyawan aktif')
                ->descriptionIcon('heroicon-m-users')
                ->chart([12, 15, 14, 18, 20, 19, 24]),
            Stat::make('Total Keluarga', (string) EmployeeFamily::query()->count())
                ->description('Relasi keluarga terdaftar')
                ->descriptionIcon('heroicon-m-heart')
                ->chart([8, 12, 14, 17, 18, 20, 22]),
            Stat::make('Dokumen Karyawan', (string) EmployeeDocument::query()->count())
                ->description('Dokumen yang tercatat')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success')
                ->chart([4, 7, 11, 13, 14, 16, 18]),
        ];
    }
}
