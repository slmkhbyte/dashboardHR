<?php

namespace App\Filament\Resources\EmploymentStatuses\Pages;

use App\Filament\Resources\EmploymentStatuses\EmploymentStatusResource;
use App\Filament\Resources\Support\Pages\CreateRecordAndRedirectToIndex;

class CreateEmploymentStatus extends CreateRecordAndRedirectToIndex
{
    protected static string $resource = EmploymentStatusResource::class;
}
