<?php

namespace App\Filament\Resources\EmployeeFamilies\Pages;

use App\Filament\Resources\EmployeeFamilies\EmployeeFamilyResource;
use App\Filament\Resources\Support\Pages\CreateRecordAndRedirectToIndex;

class CreateEmployeeFamily extends CreateRecordAndRedirectToIndex
{
    protected static string $resource = EmployeeFamilyResource::class;
}
