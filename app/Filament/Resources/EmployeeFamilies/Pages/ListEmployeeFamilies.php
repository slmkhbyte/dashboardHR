<?php

namespace App\Filament\Resources\EmployeeFamilies\Pages;

use App\Filament\Imports\EmployeeFamilyImporter;
use App\Filament\Resources\EmployeeFamilies\EmployeeFamilyResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeFamilies extends ListRecords
{
    protected static string $resource = EmployeeFamilyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ImportAction::make()
                ->label('Impor CSV')
                ->importer(EmployeeFamilyImporter::class)
                ->successRedirectUrl(EmployeeFamilyResource::getUrl('imports'))
                ->slideOver(),
            Action::make('importHistory')
                ->label('Riwayat Import')
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->url(EmployeeFamilyResource::getUrl('imports')),
        ];
    }
}
