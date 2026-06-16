<?php

namespace App\Filament\Resources\EmployeeSapSnapshots\Pages;

use App\Filament\Resources\EmployeeSapSnapshots\EmployeeSapSnapshotResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployeeSapSnapshot extends ViewRecord
{
    protected static string $resource = EmployeeSapSnapshotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(EmployeeSapSnapshotResource::getUrl()),
        ];
    }
}
