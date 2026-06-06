<?php

namespace App\Http\Controllers;

use App\Models\EmployeeDocumentHistory;
use App\Support\EmployeeDocumentImageStorage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmployeeDocumentHistoryImageController extends Controller
{
    public function show(Request $request, EmployeeDocumentHistory $employeeDocumentHistory, string $version): Response
    {
        abort_unless(auth()->check(), 403);
        abort_unless(in_array($version, ['old', 'new'], true), 404);

        $blobAttribute = "{$version}_image_blob";
        $mimeAttribute = "{$version}_image_mime_type";
        $filenameAttribute = "{$version}_image_original_filename";
        $sizeAttribute = "{$version}_image_size_bytes";

        abort_if(blank($employeeDocumentHistory->{$blobAttribute}), 404);

        $binaryContents = EmployeeDocumentImageStorage::decodeBlobFromDatabase($employeeDocumentHistory->{$blobAttribute});
        $isDownload = $request->boolean('download');

        return response($binaryContents, 200, [
            'Content-Type' => $employeeDocumentHistory->{$mimeAttribute} ?? 'application/octet-stream',
            'Content-Length' => (string) ($employeeDocumentHistory->{$sizeAttribute} ?? strlen($binaryContents ?? '')),
            'Content-Disposition' => ($isDownload ? 'attachment' : 'inline') . '; filename="' . ($employeeDocumentHistory->{$filenameAttribute} ?? 'document-history-image') . '"',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
