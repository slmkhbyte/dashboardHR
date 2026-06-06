<?php

namespace App\Filament\Resources\EmploymentStatuses\Pages;

use App\Filament\Resources\EmploymentStatuses\EmploymentStatusResource;
use App\Filament\Resources\Support\Pages\EditRecordAndRedirectToIndex;
use App\Models\EmploymentStatus;

class EditEmploymentStatus extends EditRecordAndRedirectToIndex
{
    protected static string $resource = EmploymentStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getDeleteAction()
                ->hidden(fn (EmploymentStatus $record): bool => $record->isDefault()),
        ];
    }
}
