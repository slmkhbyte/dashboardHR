<?php

namespace App\Filament\Resources\EmployeeDocuments\Pages;

use App\Filament\Resources\EmployeeDocuments\EmployeeDocumentResource;
use App\Filament\Resources\Support\Pages\EditRecordAndRedirectToIndex;

class EditEmployeeDocument extends EditRecordAndRedirectToIndex
{
    protected static string $resource = EmployeeDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getDeleteAction(),
        ];
    }
}
