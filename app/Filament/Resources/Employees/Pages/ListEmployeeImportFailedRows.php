<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Imports\EmployeeImporter;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Filament\Resources\Support\Pages\FailedImportRowsPage;

class ListEmployeeImportFailedRows extends FailedImportRowsPage
{
    protected static string $resource = EmployeeResource::class;

    protected static function getImporterClass(): string
    {
        return EmployeeImporter::class;
    }

    protected static function getImportHistoryPageName(): string
    {
        return 'imports';
    }
}
