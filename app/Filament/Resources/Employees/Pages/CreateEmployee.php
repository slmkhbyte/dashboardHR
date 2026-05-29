<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Filament\Resources\Support\Pages\CreateRecordAndRedirectToIndex;

class CreateEmployee extends CreateRecordAndRedirectToIndex
{
    protected static string $resource = EmployeeResource::class;
}
