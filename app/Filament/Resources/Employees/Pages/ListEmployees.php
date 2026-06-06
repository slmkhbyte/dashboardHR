<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Imports\EmployeeImporter;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\Employee;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ImportAction::make()
                ->label('Impor CSV')
                ->importer(EmployeeImporter::class)
                ->successRedirectUrl(EmployeeResource::getUrl('imports'))
                ->slideOver(),
            Action::make('importHistory')
                ->label('Riwayat Import')
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->url(EmployeeResource::getUrl('imports')),
        ];
    }

    public function getTabs(): array
    {
        return [
            'active' => Tab::make('Aktif')
                ->badge((string) Employee::query()->active()->count())
                ->modifyQueryUsing(fn ($query) => $query->active()),
            'inactive' => Tab::make('Nonaktif')
                ->badge((string) Employee::query()->inactive()->count())
                ->modifyQueryUsing(fn ($query) => $query->inactive()),
            'all' => Tab::make('Semua')
                ->badge((string) Employee::query()->count()),
        ];
    }
}
