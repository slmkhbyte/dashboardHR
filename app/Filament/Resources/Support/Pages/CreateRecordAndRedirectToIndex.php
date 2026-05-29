<?php

namespace App\Filament\Resources\Support\Pages;

use Filament\Resources\Pages\CreateRecord;

abstract class CreateRecordAndRedirectToIndex extends CreateRecord
{
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl();
    }
}
