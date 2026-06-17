<?php

namespace App\Filament\Resources\EmployeeSapSnapshots\Pages;

use App\Filament\Imports\EmployeeSapSnapshotRowImporter;
use App\Filament\Resources\EmployeeSapSnapshots\EmployeeSapSnapshotResource;
use App\Filament\Resources\Support\Pages\FailedImportRowsPage;

class ListEmployeeSapSnapshotImportFailedRows extends FailedImportRowsPage
{
    protected static string $resource = EmployeeSapSnapshotResource::class;

    protected static function getImporterClass(): string
    {
        return EmployeeSapSnapshotRowImporter::class;
    }

    protected static function getImportHistoryPageName(): string
    {
        return 'imports';
    }
}
