<?php

namespace App\Filament\Resources\EmployeeSapSnapshots\Pages;

use App\Filament\Imports\EmployeeSapSnapshotRowImporter;
use App\Filament\Resources\EmployeeSapSnapshots\EmployeeSapSnapshotResource;
use App\Filament\Resources\Support\Pages\ImportHistoryPage;

class ListEmployeeSapSnapshotImports extends ImportHistoryPage
{
    protected static string $resource = EmployeeSapSnapshotResource::class;

    protected static function getImporterClass(): string
    {
        return EmployeeSapSnapshotRowImporter::class;
    }

    protected static function getImportFailuresPageName(): string
    {
        return 'failed-import-rows';
    }
}
