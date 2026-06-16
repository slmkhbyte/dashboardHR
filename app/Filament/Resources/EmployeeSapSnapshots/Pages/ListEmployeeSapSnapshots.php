<?php

namespace App\Filament\Resources\EmployeeSapSnapshots\Pages;

use App\Filament\Resources\EmployeeSapSnapshots\EmployeeSapSnapshotResource;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeSapSnapshots extends ListRecords
{
    protected static string $resource = EmployeeSapSnapshotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EmployeeSapSnapshotResource::importAction(),
        ];
    }
}
