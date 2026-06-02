<?php

namespace App\Filament\Resources\HguMarkers\Pages;

use App\Filament\Resources\HguMarkers\HguMarkerResource;
use App\Filament\Resources\Support\Pages\EditRecordAndRedirectToIndex;

class EditHguMarker extends EditRecordAndRedirectToIndex
{
    protected static string $resource = HguMarkerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getDeleteAction(),
        ];
    }
}
