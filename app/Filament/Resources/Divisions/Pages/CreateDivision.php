<?php

namespace App\Filament\Resources\Divisions\Pages;

use App\Filament\Resources\Divisions\DivisionResource;
use App\Filament\Resources\Support\Pages\CreateRecordAndRedirectToIndex;

class CreateDivision extends CreateRecordAndRedirectToIndex
{
    protected static string $resource = DivisionResource::class;
}
