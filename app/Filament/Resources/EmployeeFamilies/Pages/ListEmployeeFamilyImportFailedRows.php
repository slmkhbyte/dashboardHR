<?php

namespace App\Filament\Resources\EmployeeFamilies\Pages;

use App\Filament\Imports\EmployeeFamilyImporter;
use App\Filament\Resources\EmployeeFamilies\EmployeeFamilyResource;
use App\Filament\Resources\Support\Pages\FailedImportRowsPage;

class ListEmployeeFamilyImportFailedRows extends FailedImportRowsPage
{
    protected static string $resource = EmployeeFamilyResource::class;

    protected static function getImporterClass(): string
    {
        return EmployeeFamilyImporter::class;
    }

    protected static function getImportHistoryPageName(): string
    {
        return 'imports';
    }
}
