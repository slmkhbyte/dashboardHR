<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Imports\EmployeeImporter;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Filament\Resources\Support\Pages\ImportHistoryPage;

class ListEmployeeImports extends ImportHistoryPage
{
    protected static string $resource = EmployeeResource::class;

    protected static function getImporterClass(): string
    {
        return EmployeeImporter::class;
    }

    protected static function getImportFailuresPageName(): string
    {
        return 'failed-import-rows';
    }
}
