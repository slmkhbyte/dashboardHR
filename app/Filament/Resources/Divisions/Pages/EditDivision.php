<?php

namespace App\Filament\Resources\Divisions\Pages;

use App\Filament\Resources\Divisions\DivisionResource;
use App\Filament\Resources\Support\Pages\EditRecordAndRedirectToIndex;

class EditDivision extends EditRecordAndRedirectToIndex
{
    protected static string $resource = DivisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getDeleteAction(),
        ];
    }
}
