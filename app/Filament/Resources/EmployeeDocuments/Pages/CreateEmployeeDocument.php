<?php

namespace App\Filament\Resources\EmployeeDocuments\Pages;

use App\Filament\Resources\EmployeeDocuments\EmployeeDocumentResource;
use App\Filament\Resources\Support\Pages\CreateRecordAndRedirectToIndex;
use App\Support\EmployeeDocumentImageStorage;

class CreateEmployeeDocument extends CreateRecordAndRedirectToIndex
{
    protected static string $resource = EmployeeDocumentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $path = $data['image_upload'] ?? null;

        if (blank($path)) {
            unset($data['image_upload']);

            return $data;
        }

        try {
            $data = array_merge($data, EmployeeDocumentImageStorage::buildDatabasePayload($path));
        } finally {
            EmployeeDocumentImageStorage::deleteTempFile($path);
        }

        unset($data['image_upload']);

        return $data;
    }
}
