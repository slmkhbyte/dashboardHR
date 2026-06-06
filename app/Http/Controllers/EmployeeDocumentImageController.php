<?php

namespace App\Http\Controllers;

use App\Models\EmployeeDocument;
use App\Support\EmployeeDocumentImageStorage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmployeeDocumentImageController extends Controller
{
    public function show(Request $request, EmployeeDocument $employeeDocument): Response
    {
        abort_unless(auth()->check(), 403);
        abort_if(blank($employeeDocument->image_blob), 404);

        $binaryContents = EmployeeDocumentImageStorage::decodeBlobFromDatabase($employeeDocument->image_blob);
        $isDownload = $request->boolean('download');

        return response($binaryContents, 200, [
            'Content-Type' => $employeeDocument->image_mime_type ?? 'application/octet-stream',
            'Content-Length' => (string) ($employeeDocument->image_size_bytes ?? strlen($binaryContents ?? '')),
            'Content-Disposition' => ($isDownload ? 'attachment' : 'inline') . '; filename="' . ($employeeDocument->image_original_filename ?? 'document-image') . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
