<?php

namespace App\Filament\Resources\Support\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

abstract class EditRecordAndRedirectToIndex extends EditRecord
{
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl();
    }

    protected function getDeleteAction(): DeleteAction
    {
        return DeleteAction::make()
            ->successRedirectUrl(static::getResource()::getUrl());
    }
}
