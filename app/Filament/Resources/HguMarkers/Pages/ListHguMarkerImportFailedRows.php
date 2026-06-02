<?php

namespace App\Filament\Resources\HguMarkers\Pages;

use App\Filament\Imports\HguMarkerImporter;
use App\Filament\Resources\HguMarkers\HguMarkerResource;
use App\Filament\Resources\Support\Pages\FailedImportRowsPage;

class ListHguMarkerImportFailedRows extends FailedImportRowsPage
{
    protected static string $resource = HguMarkerResource::class;

    protected static function getImporterClass(): string
    {
        return HguMarkerImporter::class;
    }

    protected static function getImportHistoryPageName(): string
    {
        return 'imports';
    }
}
