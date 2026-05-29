<?php

namespace App\Filament\Resources\EmployeeFamilies\Pages;

use App\Filament\Resources\EmployeeFamilies\EmployeeFamilyResource;
use App\Filament\Resources\Support\Pages\EditRecordAndRedirectToIndex;

class EditEmployeeFamily extends EditRecordAndRedirectToIndex
{
    protected static string $resource = EmployeeFamilyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getDeleteAction(),
        ];
    }
}
