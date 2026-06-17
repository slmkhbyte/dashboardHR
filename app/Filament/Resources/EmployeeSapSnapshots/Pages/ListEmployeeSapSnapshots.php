<?php

namespace App\Filament\Resources\EmployeeSapSnapshots\Pages;

use App\Filament\Imports\EmployeeSapSnapshotRowImporter;
use App\Filament\Resources\EmployeeSapSnapshots\EmployeeSapSnapshotResource;
use Filament\Actions\Action;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeSapSnapshots extends ListRecords
{
    protected static string $resource = EmployeeSapSnapshotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->label('Impor Data SAP')
                ->importer(EmployeeSapSnapshotRowImporter::class)
                ->successRedirectUrl(EmployeeSapSnapshotResource::getUrl('imports'))
                ->slideOver(),
            Action::make('importHistory')
                ->label('Riwayat Import')
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->url(EmployeeSapSnapshotResource::getUrl('imports')),
        ];
    }
}
