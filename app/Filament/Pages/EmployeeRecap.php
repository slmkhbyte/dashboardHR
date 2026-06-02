<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\EmployeeRecapSummary;
use BackedEnum;
use UnitEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class EmployeeRecap extends Page
{
    protected string $view = 'filament.pages.employee-recap';

    protected static ?string $navigationLabel = 'Dashboard Karyawan';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string | UnitEnum | null $navigationGroup = 'Operasional HR';

    protected static ?int $navigationSort = 0;

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }
}
