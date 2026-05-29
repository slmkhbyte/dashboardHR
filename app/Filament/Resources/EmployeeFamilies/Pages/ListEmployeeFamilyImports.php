<?php

namespace App\Filament\Resources\EmployeeFamilies\Pages;

use App\Filament\Imports\EmployeeFamilyImporter;
use App\Filament\Resources\EmployeeFamilies\EmployeeFamilyResource;
use App\Filament\Resources\Support\Pages\ImportHistoryPage;

class ListEmployeeFamilyImports extends ImportHistoryPage
{
    protected static string $resource = EmployeeFamilyResource::class;

    protected static function getImporterClass(): string
    {
        return EmployeeFamilyImporter::class;
    }

    protected static function getImportFailuresPageName(): string
    {
        return 'failed-import-rows';
    }
}
