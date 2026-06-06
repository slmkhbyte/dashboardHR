<?php

namespace App\Filament\Resources\EmployeeDocuments\Pages;

use App\Filament\Resources\EmployeeDocuments\EmployeeDocumentResource;
use App\Filament\Resources\Support\Pages\EditRecordAndRedirectToIndex;
use App\Support\EmployeeDocumentImageStorage;

class EditEmployeeDocument extends EditRecordAndRedirectToIndex
{
    protected static string $resource = EmployeeDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getDeleteAction(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $path = $data['image_upload'] ?? null;

        if (filled($path)) {
            try {
                $data = array_merge($data, EmployeeDocumentImageStorage::buildDatabasePayload($path));
            } finally {
                EmployeeDocumentImageStorage::deleteTempFile($path);
            }
        }

        unset($data['image_upload']);

        return $data;
    }
}
