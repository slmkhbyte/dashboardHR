<?php

namespace App\Filament\Resources\Positions\Pages;

use App\Filament\Resources\Positions\PositionResource;
use App\Filament\Resources\Support\Pages\CreateRecordAndRedirectToIndex;

class CreatePosition extends CreateRecordAndRedirectToIndex
{
    protected static string $resource = PositionResource::class;
}
