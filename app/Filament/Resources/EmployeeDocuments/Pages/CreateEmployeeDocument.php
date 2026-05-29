<?php

namespace App\Filament\Resources\EmployeeDocuments\Pages;

use App\Filament\Resources\EmployeeDocuments\EmployeeDocumentResource;
use App\Filament\Resources\Support\Pages\CreateRecordAndRedirectToIndex;

class CreateEmployeeDocument extends CreateRecordAndRedirectToIndex
{
    protected static string $resource = EmployeeDocumentResource::class;
}
