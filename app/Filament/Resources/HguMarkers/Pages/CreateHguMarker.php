<?php

namespace App\Filament\Resources\HguMarkers\Pages;

use App\Filament\Resources\HguMarkers\HguMarkerResource;
use App\Filament\Resources\Support\Pages\CreateRecordAndRedirectToIndex;

class CreateHguMarker extends CreateRecordAndRedirectToIndex
{
    protected static string $resource = HguMarkerResource::class;
}
