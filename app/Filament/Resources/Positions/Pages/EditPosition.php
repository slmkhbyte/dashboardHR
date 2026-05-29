<?php

namespace App\Filament\Resources\Positions\Pages;

use App\Filament\Resources\Positions\PositionResource;
use App\Filament\Resources\Support\Pages\EditRecordAndRedirectToIndex;

class EditPosition extends EditRecordAndRedirectToIndex
{
    protected static string $resource = PositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getDeleteAction(),
        ];
    }
}
